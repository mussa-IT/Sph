<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\TemplatePurchase;
use App\Models\TemplateReview;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index(Request $request): View
    {
        $categories = TemplateCategory::active()->ordered()->get();
        $featuredTemplates = Template::approved()->featured()->with(['seller', 'category'])->take(6)->get();
        
        $query = Template::approved()->with(['seller', 'category']);
        
        if ($request->get('category')) {
            $query->byCategory($request->get('category'));
        }
        
        if ($request->get('search')) {
            $query->search($request->get('search'));
        }
        
        if ($request->get('sort')) {
            $query->sortBy($request->get('sort'));
        }
        
        if ($request->get('price')) {
            if ($request->get('price') === 'free') {
                $query->free();
            } elseif ($request->get('price') === 'paid') {
                $query->paid();
            }
        }
        
        $templates = $query->paginate(12);
        
        return view('pages.templates.index', compact(
            'categories',
            'featuredTemplates',
            'templates'
        ));
    }

    public function show(Request $request, string $slug): View
    {
        $template = Template::where('slug', $slug)
            ->with(['seller', 'category', 'reviews.user'])
            ->firstOrFail();
        
        if (!$template->isApproved() && $template->seller_id !== Auth::id()) {
            abort(404);
        }
        
        $relatedTemplates = Template::approved()
            ->where('category_id', $template->category_id)
            ->where('id', '!=', $template->id)
            ->take(4)
            ->get();
        
        $reviews = $template->reviews()
            ->with('user')
            ->latest()
            ->paginate(10);
        
        return view('pages.templates.show', compact(
            'template',
            'relatedTemplates',
            'reviews'
        ));
    }

    public function create(): View
    {
        $categories = TemplateCategory::active()->ordered()->get();
        
        return view('pages.templates.create', compact('categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:template_categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'tags' => ['nullable', 'array'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'preview' => ['nullable', 'image', 'max:2048'],
        ]);

        $template = Template::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'currency' => $request->currency,
            'tags' => $request->tags ?? [],
            'seller_id' => Auth::id(),
            'status' => 'pending', // Requires approval
        ]);

        // Handle file uploads
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('templates/thumbnails', 'public');
            $template->update(['thumbnail_url' => Storage::url($thumbnailPath)]);
        }

        if ($request->hasFile('preview')) {
            $previewPath = $request->file('preview')->store('templates/previews', 'public');
            $template->update(['preview_url' => Storage::url($previewPath)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Template submitted for approval!',
            'template' => $template,
        ]);
    }

    public function edit(Request $request, Template $template): View
    {
        if ($template->seller_id !== Auth::id()) {
            abort(403);
        }

        $categories = TemplateCategory::active()->ordered()->get();
        
        return view('pages.templates.edit', compact('template', 'categories'));
    }

    public function update(Request $request, Template $template): JsonResponse
    {
        if ($template->seller_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], 403);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'category_id' => ['required', 'exists:template_categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'tags' => ['nullable', 'array'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'preview' => ['nullable', 'image', 'max:2048'],
        ]);

        $template->update($request->only([
            'title',
            'description',
            'content',
            'category_id',
            'price',
            'currency',
            'tags',
        ]));

        // Handle file uploads
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('templates/thumbnails', 'public');
            $template->update(['thumbnail_url' => Storage::url($thumbnailPath)]);
        }

        if ($request->hasFile('preview')) {
            $previewPath = $request->file('preview')->store('templates/previews', 'public');
            $template->update(['preview_url' => Storage::url($previewPath)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully!',
            'template' => $template,
        ]);
    }

    public function purchase(Request $request, Template $template): JsonResponse
    {
        $user = Auth::user();
        
        if (!$template->canBePurchasedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'This template cannot be purchased.',
            ]);
        }

        // Create purchase record
        $platformFee = $template->price * 0.15; // 15% platform fee
        $sellerEarnings = $template->price - $platformFee;

        $purchase = TemplatePurchase::create([
            'template_id' => $template->id,
            'buyer_id' => $user->id,
            'seller_id' => $template->seller_id,
            'price' => $template->price,
            'currency' => $template->currency,
            'payment_gateway' => 'stripe', // TODO: Implement payment gateway
            'platform_fee' => $platformFee,
            'seller_earnings' => $sellerEarnings,
        ]);

        // TODO: Process payment with payment gateway
        
        return response()->json([
            'success' => true,
            'message' => 'Purchase initiated successfully!',
            'purchase' => $purchase,
        ]);
    }

    public function download(Request $request, Template $template): JsonResponse
    {
        $user = Auth::user();
        
        $purchase = TemplatePurchase::where('template_id', $template->id)
            ->where('buyer_id', $user->id)
            ->where('status', 'completed')
            ->first();

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'You have not purchased this template.',
            ]);
        }

        // Increment download count
        $template->incrementDownloads();

        return response()->json([
            'success' => true,
            'content' => $template->content,
            'message' => 'Template downloaded successfully!',
        ]);
    }

    public function review(Request $request, Template $template): JsonResponse
    {
        $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        
        // Check if user has purchased the template
        $hasPurchased = TemplatePurchase::where('template_id', $template->id)
            ->where('buyer_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You must purchase this template to leave a review.',
            ]);
        }

        // Check if user has already reviewed
        $existingReview = TemplateReview::where('template_id', $template->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this template.',
            ]);
        }

        $review = TemplateReview::create([
            'template_id' => $template->id,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully!',
            'review' => $review,
        ]);
    }

    public function myTemplates(): View
    {
        $user = Auth::user();
        
        $templates = Template::where('seller_id', $user->id)
            ->with(['category', 'purchases'])
            ->latest()
            ->paginate(12);

        return view('pages.templates.my-templates', compact('templates'));
    }

    public function myPurchases(): View
    {
        $user = Auth::user();
        
        $purchases = TemplatePurchase::where('buyer_id', $user->id)
            ->with(['template.seller', 'template.category'])
            ->latest()
            ->paginate(12);

        return view('pages.templates.my-purchases', compact('purchases'));
    }
}
