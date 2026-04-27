<?php

namespace App\Services;

use App\Services\OpenAI\OpenAIClient;
use Throwable;

class AIService implements AIServiceInterface
{
    public function __construct(private OpenAIClient $openAIClient)
    {
    }

    public function chat(string $message, array $context = []): array
    {
        $content = trim($message);
        $normalizedContent = $this->normalizeLocalExpressions($content);
        $projectType = $this->determineProjectType(strtolower($normalizedContent));
        $languageMode = $this->detectLanguageMode($content);
        $promptPackage = $this->buildPromptPackage($content, $normalizedContent, $languageMode, $context);
        $fallbackReply = $this->buildContextualReply($languageMode, $projectType, $context);
        $reply = $fallbackReply;
        $meta = $this->openAiPlaceholder('chat');
        $meta['source'] = 'fallback';

        if ($this->openAIClient->isConfigured()) {
            try {
                $messages = $this->buildConversationMessages($promptPackage, $context);
                $completion = $this->openAIClient->createChatCompletion($messages);

                $liveReply = trim((string) ($completion['reply'] ?? ''));
                if ($liveReply !== '') {
                    $reply = $liveReply;
                    $meta = array_merge($meta, [
                        'source' => 'live_openai',
                        'model' => (string) ($completion['model'] ?? config('services.openai.model', 'gpt-4o-mini')),
                    ]);
                }
            } catch (Throwable $exception) {
                report($exception);
                $meta = array_merge($meta, [
                    'source' => 'fallback_after_openai_error',
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return [
            'input' => [
                'message' => $content,
                'normalized_message' => $normalizedContent,
            ],
            'reply' => $reply,
            'suggested_next_prompts' => $this->buildContextualPrompts($languageMode, $context),
            'meta' => $meta,
            'language' => [
                'mode' => $languageMode,
                'auto_detected' => true,
            ],
            'prompt_package' => $promptPackage,
        ];
    }

    public function analyzeProject(string $idea): array
    {
        $normalizedIdea = strtolower(trim($idea));
        $projectType = $this->determineProjectType($normalizedIdea);
        $difficulty = $this->determineDifficulty($normalizedIdea);
        $timelineWeeks = $this->estimateTimeline($difficulty);

        return [
            'idea' => trim($idea),
            'summary' => $this->summarizeIdea($idea, $projectType),
            'project_type' => $projectType,
            'difficulty' => $difficulty,
            'estimated_timeline' => [
                'weeks' => $timelineWeeks,
                'label' => $timelineWeeks . ' weeks (estimated)',
            ],
            'step_by_step_plan' => $this->generateStepByStepPlan($projectType, $difficulty),
            'risks' => [
                'Scope creep from unclear MVP boundaries',
                'Delivery delays if dependencies are not mapped early',
                'Budget variance without milestone-based tracking',
            ],
            'next_actions' => [
                'Define MVP acceptance criteria',
                'Confirm delivery milestones and owners',
                'Set a weekly review cadence',
            ],
            'meta' => $this->openAiPlaceholder('analyzeProject'),
        ];
    }

    public function generateBudget(string $idea): array
    {
        $normalizedIdea = strtolower(trim($idea));
        $projectType = $this->determineProjectType($normalizedIdea);
        $difficulty = $this->determineDifficulty($normalizedIdea);
        $estimatedBudget = $this->estimateBudget($projectType, $difficulty);
        $minimumBudget = (int) $estimatedBudget['min'];
        $idealBudget = (int) $estimatedBudget['max'];

        $componentBreakdown = [
            [
                'component' => 'Planning & Discovery',
                'minimum_cost' => (int) round($minimumBudget * 0.10),
                'ideal_cost' => (int) round($idealBudget * 0.12),
            ],
            [
                'component' => 'Design & UX',
                'minimum_cost' => (int) round($minimumBudget * 0.15),
                'ideal_cost' => (int) round($idealBudget * 0.18),
            ],
            [
                'component' => 'Development',
                'minimum_cost' => (int) round($minimumBudget * 0.50),
                'ideal_cost' => (int) round($idealBudget * 0.52),
            ],
            [
                'component' => 'QA & Testing',
                'minimum_cost' => (int) round($minimumBudget * 0.12),
                'ideal_cost' => (int) round($idealBudget * 0.10),
            ],
            [
                'component' => 'Deployment & Operations',
                'minimum_cost' => (int) round($minimumBudget * 0.13),
                'ideal_cost' => (int) round($idealBudget * 0.08),
            ],
        ];

        return [
            'idea' => trim($idea),
            'currency' => 'USD',
            'minimum_budget' => $minimumBudget,
            'ideal_budget' => $idealBudget,
            'component_cost_breakdown' => $componentBreakdown,
            'cost_saving_alternatives' => [
                'Use open-source starter templates for the first release',
                'Prioritize MVP scope and defer non-core integrations',
                'Choose free-tier infrastructure during validation stage',
                'Use reusable UI component libraries to reduce design hours',
            ],
            'estimated_hours' => (int) $estimatedBudget['estimated_hours'],
            // Backward-compatible aliases used by existing UI/controller mappings.
            'range' => [
                'min' => $minimumBudget,
                'max' => $idealBudget,
            ],
            'breakdown' => array_map(function (array $item): array {
                return [
                    'category' => $item['component'],
                    'min' => $item['minimum_cost'],
                    'max' => $item['ideal_cost'],
                ];
            }, $componentBreakdown),
            'assumptions' => [
                'Single product team with consistent sprint velocity',
                'No major third-party licensing surprises',
                'MVP-first scope with phased feature rollout',
            ],
            'meta' => $this->openAiPlaceholder('generateBudget'),
        ];
    }

    public function suggestTools(string $idea): array
    {
        $normalizedIdea = strtolower(trim($idea));
        $projectType = $this->determineProjectType($normalizedIdea);
        $toolsByCategory = $this->generateToolsList($projectType);
        $recommendations = $this->generateToolRecommendations($projectType, $toolsByCategory);

        $categories = [];
        foreach ($toolsByCategory as $category => $tools) {
            $categories[] = [
                'category' => $category,
                'tools' => array_values($tools),
            ];
        }

        return [
            'idea' => trim($idea),
            'project_type' => $projectType,
            'categories' => $categories,
            'primary_tools' => $recommendations['primary_tools'],
            'cheap_alternatives' => $recommendations['cheap_alternatives'],
            'free_software_alternatives' => $recommendations['free_software_alternatives'],
            'diy_options' => $recommendations['diy_options'],
            'local_sourcing_suggestions' => $recommendations['local_sourcing_suggestions'],
            'recommended_stack' => [
                'frontend' => $toolsByCategory['Frontend'][0] ?? 'React',
                'backend' => $toolsByCategory['Backend'][0] ?? 'Laravel',
                'database' => $toolsByCategory['Database'][0] ?? 'MySQL',
                'devops' => $toolsByCategory['Ops'][0] ?? ($toolsByCategory['DevOps'][0] ?? 'Docker'),
            ],
            'meta' => $this->openAiPlaceholder('suggestTools'),
        ];
    }

    public function analyzeProjectIdea(string $idea): array
    {
        $analysis = $this->analyzeProject($idea);

        return [
            'idea_summary' => $analysis['summary'],
            'project_type' => $analysis['project_type'],
            'difficulty' => $analysis['difficulty'],
            'estimated_budget' => $this->generateBudget($idea),
            'tools_list' => $this->suggestTools($idea),
            'step_by_step_plan' => $analysis['step_by_step_plan'],
            'confidence_score' => 88,
            'feasibility_score' => 84,
            'estimated_timeline_weeks' => $analysis['estimated_timeline']['weeks'],
            'success_factors' => [
                'Clear scope definition before build',
                'Regular stakeholder review loops',
                'Measured rollout with telemetry',
            ],
            'next_actions' => $analysis['next_actions'],
            'meta' => $analysis['meta'],
        ];
    }

    private function summarizeIdea(string $idea, string $projectType): string
    {
        return trim($idea) !== ''
            ? trim($idea)
            : 'A scoped ' . $projectType . ' focused on early validation and iterative delivery.';
    }

    private function determineProjectType(string $idea): string
    {
        if (str_contains($idea, 'ecommerce') || str_contains($idea, 'shop') || str_contains($idea, 'store')) {
            return 'E-commerce Platform';
        }

        if (str_contains($idea, 'mobile') || str_contains($idea, 'android') || str_contains($idea, 'ios')) {
            return 'Mobile Application';
        }

        if (str_contains($idea, 'dashboard') || str_contains($idea, 'analytics') || str_contains($idea, 'data')) {
            return 'Data Dashboard';
        }

        if (str_contains($idea, 'api') || str_contains($idea, 'service') || str_contains($idea, 'backend')) {
            return 'API/Service';
        }

        if (str_contains($idea, 'game') || str_contains($idea, 'gaming')) {
            return 'Game Development';
        }

        if (str_contains($idea, 'web') || str_contains($idea, 'website') || str_contains($idea, 'app')) {
            return 'Web Application';
        }

        return 'Custom Software Solution';
    }

    private function determineDifficulty(string $idea): string
    {
        $complexityIndicators = [
            'machine learning', 'ai', 'artificial intelligence', 'blockchain',
            'real-time', 'scalability', 'high traffic', 'complex algorithm',
            'integration', 'multiple systems', 'advanced',
        ];

        $simpleIndicators = [
            'simple', 'basic', 'straightforward', 'landing page', 'portfolio',
        ];

        foreach ($complexityIndicators as $indicator) {
            if (str_contains($idea, $indicator)) {
                return 'Advanced';
            }
        }

        foreach ($simpleIndicators as $indicator) {
            if (str_contains($idea, $indicator)) {
                return 'Beginner';
            }
        }

        return 'Intermediate';
    }

    private function estimateBudget(string $projectType, string $difficulty): array
    {
        $baseBudgets = [
            'Web Application' => ['min' => 5000, 'max' => 25000],
            'Mobile Application' => ['min' => 8000, 'max' => 35000],
            'API/Service' => ['min' => 3000, 'max' => 15000],
            'E-commerce Platform' => ['min' => 10000, 'max' => 50000],
            'Data Dashboard' => ['min' => 4000, 'max' => 20000],
            'Game Development' => ['min' => 15000, 'max' => 75000],
            'Custom Software Solution' => ['min' => 7000, 'max' => 30000],
        ];

        $budget = $baseBudgets[$projectType] ?? $baseBudgets['Custom Software Solution'];
        $multiplier = match ($difficulty) {
            'Beginner' => 0.75,
            'Intermediate' => 1.0,
            'Advanced' => 1.5,
            default => 1.0,
        };

        return [
            'min' => (int) round($budget['min'] * $multiplier),
            'max' => (int) round($budget['max'] * $multiplier),
            'estimated_hours' => match ($difficulty) {
                'Beginner' => 120,
                'Intermediate' => 240,
                'Advanced' => 420,
                default => 240,
            },
        ];
    }

    private function generateToolsList(string $projectType): array
    {
        $toolsets = [
            'Web Application' => [
                'Frontend' => ['React', 'Vue.js', 'Tailwind CSS'],
                'Backend' => ['Laravel', 'Node.js'],
                'Database' => ['MySQL', 'PostgreSQL'],
                'Ops' => ['Docker', 'GitHub Actions', 'AWS'],
            ],
            'Mobile Application' => [
                'Frontend' => ['React Native', 'Flutter'],
                'Backend' => ['Laravel API', 'Firebase'],
                'Database' => ['SQLite', 'PostgreSQL'],
                'Ops' => ['Fastlane', 'TestFlight'],
            ],
            'API/Service' => [
                'Backend' => ['Laravel', 'FastAPI'],
                'Database' => ['PostgreSQL', 'Redis'],
                'Ops' => ['Docker', 'Kubernetes'],
            ],
            'E-commerce Platform' => [
                'Frontend' => ['React', 'Next.js'],
                'Backend' => ['Laravel', 'Shopify API'],
                'Database' => ['MySQL', 'PostgreSQL'],
                'Ops' => ['AWS', 'Vercel'],
            ],
            'Data Dashboard' => [
                'Frontend' => ['React', 'Chart.js'],
                'Backend' => ['Laravel', 'Node.js'],
                'Database' => ['PostgreSQL', 'TimescaleDB'],
                'Ops' => ['Docker', 'Kubernetes'],
            ],
            'Game Development' => [
                'Engine' => ['Unity', 'Godot'],
                'Language' => ['C#', 'C++'],
                'Tools' => ['Blender', 'Figma'],
            ],
        ];

        return $toolsets[$projectType] ?? [
            'General' => ['Laravel', 'React', 'MySQL', 'Docker'],
        ];
    }

    private function generateStepByStepPlan(string $projectType, string $difficulty): array
    {
        $plans = [
            'Web Application' => [
                'Define user flows and success metrics',
                'Create UX/UI wireframes for core screens',
                'Implement backend services and schema',
                'Build frontend with reusable components',
                'Test key workflows and launch incrementally',
            ],
            'Mobile Application' => [
                'Define platform scope and app architecture',
                'Prototype key screens and interactions',
                'Build API and client-side features',
                'Test on real devices and resolve edge cases',
                'Prepare release checklist for app stores',
            ],
            'API/Service' => [
                'Define API contract and authentication model',
                'Build service modules and persistence layer',
                'Add observability and automated tests',
                'Publish docs and client examples',
                'Deploy with monitoring and rollback strategy',
            ],
        ];

        $plan = $plans[$projectType] ?? [
            'Define product goals and constraints',
            'Design architecture and delivery milestones',
            'Build MVP features and validate early',
            'Harden quality with tests and monitoring',
            'Launch and iterate from feedback',
        ];

        if ($difficulty === 'Advanced') {
            array_splice($plan, 2, 0, ['Run architecture and security review']);
        }

        return $plan;
    }

    private function generateToolRecommendations(string $projectType, array $toolsByCategory): array
    {
        $primaryTools = [];
        foreach ($toolsByCategory as $tools) {
            if (is_array($tools) && isset($tools[0])) {
                $primaryTools[] = $tools[0];
            }
        }

        $cheapAlternatives = match ($projectType) {
            'Web Application' => ['Hostinger VPS', 'Namecheap Shared Hosting', 'Cloudflare free CDN'],
            'Mobile Application' => ['Firebase Spark plan', 'Supabase free tier', 'Expo free workflow'],
            'E-commerce Platform' => ['WooCommerce starter setup', 'Ecwid free plan', 'Paystack starter fees'],
            'API/Service' => ['Railway starter', 'Render free tier', 'DigitalOcean basic droplet'],
            default => ['Hetzner basic instance', 'Render starter plan', 'Namecheap domain bundles'],
        };

        $freeSoftware = match ($projectType) {
            'Web Application' => ['Laravel', 'React', 'PostgreSQL', 'Tailwind CSS'],
            'Mobile Application' => ['Flutter', 'React Native', 'SQLite', 'Figma free plan'],
            'E-commerce Platform' => ['WooCommerce', 'Medusa', 'Strapi community edition'],
            'API/Service' => ['FastAPI', 'Laravel', 'Postman free', 'Swagger UI'],
            default => ['VS Code', 'Git', 'Docker', 'PostgreSQL'],
        };

        $diyOptions = [
            'Use starter templates and customize branding internally',
            'Run QA with checklist-based manual testing before automation',
            'Create a basic design system in Figma and reuse components',
            'Deploy first release on a single low-cost server with backups',
        ];

        $localSourcing = [
            'Source a part-time local freelancer for UI polish and QA',
            'Use nearby university tech communities for internship support',
            'Purchase hardware and accessories from local ICT hubs for faster replacement',
            'Choose local payment gateways and SMS providers for better regional support',
        ];

        return [
            'primary_tools' => array_values(array_unique($primaryTools)),
            'cheap_alternatives' => $cheapAlternatives,
            'free_software_alternatives' => $freeSoftware,
            'diy_options' => $diyOptions,
            'local_sourcing_suggestions' => $localSourcing,
        ];
    }

    private function estimateTimeline(string $difficulty): int
    {
        return match ($difficulty) {
            'Beginner' => 6,
            'Intermediate' => 12,
            'Advanced' => 20,
            default => 12,
        };
    }

    private function detectLanguageMode(string $message): string
    {
        $text = strtolower($message);
        $swahiliWords = [
            'habari', 'mambo', 'tafadhali', 'asante', 'karibu', 'nisaidie', 'nataka',
            'mradi', 'wazo', 'bajeti', 'mpango', 'timeline', 'hatua', 'tengeneza',
            'naomba', 'je', 'hii', 'kwa', 'sana', 'vizuri', 'nini', 'inaweza',
            'kiswahili', 'lugha', 'weka', 'ongeza', 'panga',
        ];
        $englishWords = [
            'hello', 'please', 'thanks', 'project', 'budget', 'plan', 'timeline',
            'build', 'create', 'scope', 'feature', 'requirements', 'milestone',
            'estimate', 'roadmap', 'team', 'deliver', 'launch', 'english',
        ];

        $swCount = 0;
        $enCount = 0;

        foreach ($swahiliWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $text) === 1) {
                $swCount++;
            }
        }

        foreach ($englishWords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/u', $text) === 1) {
                $enCount++;
            }
        }

        if ($swCount > 0 && $enCount > 0) {
            return 'mixed';
        }

        if ($swCount > 0) {
            return 'sw';
        }

        return 'en';
    }

    private function buildLocalizedReply(string $languageMode, string $projectType): string
    {
        if ($languageMode === 'sw') {
            return 'Sawa, nimekuelewa vizuri. Kwa huu mradi wa ' . $projectType . ', tuanze na scope iliyo wazi, timeline inayotekelezeka, na mipaka ya bajeti ili utekelezaji uwe wa kitaalamu.';
        }

        if ($languageMode === 'mixed') {
            return 'Nimekupata vizuri. For this ' . $projectType . ', tuanze na clear scope, realistic timeline, na budget guardrails ili delivery iwe professional.';
        }

        return 'Great direction. For this ' . $projectType . ', let us start with clear scope, a realistic timeline, and budget guardrails for professional delivery.';
    }

    private function buildLocalizedPrompts(string $languageMode): array
    {
        if ($languageMode === 'sw') {
            return [
                'Nitengezee mpango wa milestones kwa wazo hili.',
                'Nikadirie bajeti na majukumu ya timu.',
                'Niorodheshee vipengele vya MVP kwa kipaumbele.',
            ];
        }

        if ($languageMode === 'mixed') {
            return [
                'Create mpango wa milestones kwa idea hii.',
                'Estimate bajeti na team roles.',
                'List MVP features kwa order ya priority.',
            ];
        }

        return [
            'Create a milestone plan for this idea.',
            'Estimate required budget and team roles.',
            'List the MVP features in priority order.',
        ];
    }

    private function normalizeLocalExpressions(string $message): string
    {
        $normalized = trim($message);

        $replacements = [
            '/\bniko stuck\b/i' => 'nimekwama',
            '/\bimehang\b/i' => 'haifanyi kazi vizuri',
            '/\bsi elewi\b/i' => 'sielewi',
            '/\bnataka\b/i' => 'nataka',
            '/\buniwekee\b/i' => 'nitengenezee',
            '/\bapp ya kuuza\b/i' => 'ecommerce app',
            '/\bapp ya delivery\b/i' => 'delivery application',
            '/\bbei ndogo\b/i' => 'low budget',
            '/\bharaka haraka\b/i' => 'quick turnaround',
            '/\bmda\b/i' => 'timeline',
            '/\btools gani\b/i' => 'suggest tools',
            '/\bnianzie wapi\b/i' => 'where should I start',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $normalized = preg_replace($pattern, $replacement, $normalized) ?? $normalized;
        }

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }

    private function buildPromptPackage(string $originalMessage, string $normalizedMessage, string $languageMode, array $context = []): array
    {
        $beginnerSignals = [
            'sielewi', 'help', 'nisaidie', 'start', 'anzie', 'first time', 'beginner', 'newbie',
            'sijui', 'guidance', 'naomba msaada',
        ];

        $lower = strtolower($normalizedMessage);
        $isBeginner = false;

        foreach ($beginnerSignals as $signal) {
            if (str_contains($lower, $signal)) {
                $isBeginner = true;
                break;
            }
        }

        $toneInstruction = $isBeginner
            ? 'Use beginner-friendly guidance with simple steps, examples, and no jargon.'
            : 'Use concise professional guidance with practical next steps.';

        $languageInstruction = match ($languageMode) {
            'sw' => 'Respond in natural Swahili.',
            'mixed' => 'Respond in natural mixed Swahili-English without sounding robotic.',
            default => 'Respond in natural English.',
        };

        $contextInstruction = '';
        if (!empty($context['recent_messages'])) {
            $contextInstruction = 'Consider the recent conversation context for continuity, but do not simply repeat previous answers. Build upon the discussion.';
        }

        return [
            'system_prompt' => implode(' ', [
                'You are a professional project assistant.',
                'Understand slang, local expressions, and imperfect phrasing.',
                $toneInstruction,
                $languageInstruction,
                $contextInstruction,
                'Provide unique, helpful responses that advance the conversation.',
            ]),
            'user_prompt' => $normalizedMessage,
            'original_user_message' => $originalMessage,
            'mode' => [
                'language' => $languageMode,
                'beginner' => $isBeginner,
            ],
            'context' => $context,
        ];
    }

    private function buildContextualReply(string $languageMode, string $projectType, array $context): string
    {
        $messageCount = count($context['recent_messages'] ?? []);
        
        if ($messageCount > 3) {
            // More contextual response for ongoing conversations
            if ($languageMode === 'sw') {
                return 'Nimekuelewa. Kulingana na tukio letu la awali, hebu tuangalie hatua ifuatayo kwa ' . $projectType . '. Je, unataka kuzingatia upande gani maalum sasa?';
            }
            
            if ($languageMode === 'mixed') {
                return 'I see what you mean. Based on our discussion, let us focus on the next steps for this ' . $projectType . '. What specific area would you like to explore next?';
            }
            
            return 'I understand. Based on our conversation, let us focus on the next steps for this ' . $projectType . '. What specific aspect would you like to explore next?';
        }
        
        // Default reply for new conversations
        return $this->buildLocalizedReply($languageMode, $projectType);
    }

    private function buildContextualPrompts(string $languageMode, array $context): array
    {
        $messageCount = count($context['recent_messages'] ?? []);
        
        if ($messageCount > 2) {
            // Contextual prompts for ongoing conversations
            if ($languageMode === 'sw') {
                return [
                    'Tuangalie hatua za utekelezaji zaidi.',
                    'Nikadirie muda na rasilimali zinazohitajika.',
                    'Tueleze changamoto zinazoweza kujitokeza.',
                ];
            }
            
            if ($languageMode === 'mixed') {
                return [
                    'Let us explore implementation details.',
                    'Estimate timeline and resources needed.',
                    'What are the potential risks?',
                ];
            }
            
            return [
                'Let us explore implementation details.',
                'Estimate timeline and resources needed.',
                'What are the potential risks?',
            ];
        }
        
        // Default prompts for new conversations
        return $this->buildLocalizedPrompts($languageMode);
    }

    private function buildConversationMessages(array $promptPackage, array $context): array
    {
        $messages = [
            ['role' => 'system', 'content' => (string) ($promptPackage['system_prompt'] ?? '')],
        ];
        
        // Add recent conversation history
        if (!empty($context['recent_messages'])) {
            foreach (array_slice($context['recent_messages'], -6) as $msg) {
                $role = $msg['sender'] === 'user' ? 'user' : 'assistant';
                $messages[] = [
                    'role' => $role,
                    'content' => $msg['message'],
                ];
            }
        }
        
        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => (string) ($promptPackage['user_prompt'] ?? ''),
        ];
        
        return $messages;
    }

    private function openAiPlaceholder(string $operation): array
    {
        $apiKey = (string) config('services.openai.key', '');
        $model = (string) config('services.openai.model', 'gpt-4o-mini');

        return [
            'provider' => 'openai',
            'model' => $model,
            'operation' => $operation,
            'configured' => $apiKey !== '',
            'api_key_env' => 'OPENAI_API_KEY',
            'model_env' => 'OPENAI_MODEL',
            'note' => $apiKey !== ''
                ? 'OpenAI key detected. Replace placeholder logic with live API call when ready.'
                : 'Set OPENAI_API_KEY in your .env to enable live OpenAI requests.',
        ];
    }
}
