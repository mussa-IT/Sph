<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FeedbackService
{
    public function createFeedback(array $feedbackData, User $user): array
    {
        $feedback = [
            'id' => uniqid('feedback_'),
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => $feedbackData['type'], // 'feature_request', 'bug_report', 'general', 'improvement'
            'category' => $feedbackData['category'],
            'title' => $feedbackData['title'],
            'description' => $feedbackData['description'],
            'priority' => $feedbackData['priority'] ?? 'medium',
            'attachments' => $feedbackData['attachments'] ?? [],
            'tags' => $feedbackData['tags'] ?? [],
            'status' => 'new',
            'votes' => 0,
            'voters' => [],
            'comments' => [],
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        // Store feedback
        $this->storeFeedback($feedback);

        // Process AI categorization
        $aiAnalysis = $this->analyzeFeedbackWithAI($feedback);
        $feedback['ai_analysis'] = $aiAnalysis;

        // Update feedback with AI analysis
        $this->updateFeedback($feedback['id'], $feedback);

        return $feedback;
    }

    public function voteOnFeedback(string $feedbackId, User $user, string $voteType): array
    {
        $feedback = $this->getFeedback($feedbackId);
        
        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        // Check if user already voted
        if (in_array($user->id, $feedback['voters'])) {
            throw new \Exception('User has already voted on this feedback');
        }

        // Add vote
        $feedback['voters'][] = $user->id;
        $feedback['votes']++;
        $feedback['updated_at'] = now()->toISOString();

        // Update feedback
        $this->updateFeedback($feedbackId, $feedback);

        // Update trending score
        $this->updateTrendingScore($feedbackId);

        return $feedback;
    }

    public function addComment(string $feedbackId, array $commentData, User $user): array
    {
        $feedback = $this->getFeedback($feedbackId);
        
        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        $comment = [
            'id' => uniqid('comment_'),
            'user_id' => $user->id,
            'user_name' => $user->name,
            'content' => $commentData['content'],
            'mentions' => $commentData['mentions'] ?? [],
            'reactions' => [],
            'created_at' => now()->toISOString(),
        ];

        $feedback['comments'][] = $comment;
        $feedback['updated_at'] = now()->toISOString();

        // Update feedback
        $this->updateFeedback($feedbackId, $feedback);

        // Notify mentioned users
        $this->notifyMentionedUsers($comment['mentions'], $feedback, $user);

        return $comment;
    }

    public function getFeedbackList(array $filters = []): array
    {
        $allFeedback = $this->getAllFeedback();
        
        // Apply filters
        $filtered = $this->applyFilters($allFeedback, $filters);
        
        // Sort by relevance (votes, recency, trending)
        $sorted = $this->sortByRelevance($filtered, $filters['sort'] ?? 'trending');

        return [
            'feedback' => $sorted,
            'total' => count($sorted),
            'filters_applied' => $filters,
            'categories' => $this->getCategories(),
            'tags' => $this->getPopularTags(),
        ];
    }

    public function getFeedbackDetails(string $feedbackId): array
    {
        $feedback = $this->getFeedback($feedbackId);
        
        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        // Add additional details
        $feedback['related_feedback'] = $this->findRelatedFeedback($feedback);
        $feedback['similar_users'] = $this->findUsersWithSimilarFeedback($feedback);
        $feedback['engagement_metrics'] = $this->calculateEngagementMetrics($feedback);

        return $feedback;
    }

    public function updateFeedbackStatus(string $feedbackId, string $status, User $user): array
    {
        $feedback = $this->getFeedback($feedbackId);
        
        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        $feedback['status'] = $status;
        $feedback['updated_at'] = now()->toISOString();
        $feedback['status_updated_by'] = $user->id;
        $feedback['status_history'][] = [
            'status' => $status,
            'updated_by' => $user->id,
            'updated_at' => now()->toISOString(),
        ];

        $this->updateFeedback($feedbackId, $feedback);

        return $feedback;
    }

    public function getTrendingFeedback(int $limit = 10): array
    {
        $allFeedback = $this->getAllFeedback();
        
        // Calculate trending scores
        $trending = array_map(function ($feedback) {
            $feedback['trending_score'] = $this->calculateTrendingScore($feedback);
            return $feedback;
        }, $allFeedback);

        // Sort by trending score
        usort($trending, function ($a, $b) {
            return $b['trending_score'] <=> $a['trending_score'];
        });

        return array_slice($trending, 0, $limit);
    }

    public function getUserFeedback(User $user, array $filters = []): array
    {
        $allFeedback = $this->getAllFeedback();
        $userFeedback = array_filter($allFeedback, function ($feedback) use ($user) {
            return $feedback['user_id'] === $user->id;
        });

        // Apply additional filters
        $filtered = $this->applyFilters($userFeedback, $filters);

        return [
            'feedback' => $filtered,
            'total' => count($filtered),
            'stats' => $this->calculateUserFeedbackStats($userFeedback),
        ];
    }

    public function generateFeedbackReport(array $filters = []): array
    {
        $allFeedback = $this->getAllFeedback();
        $filtered = $this->applyFilters($allFeedback, $filters);

        return [
            'summary' => $this->generateSummaryStats($filtered),
            'trends' => $this->analyzeTrends($filtered),
            'categories' => $this->analyzeCategories($filtered),
            'sentiment' => $this->analyzeSentiment($filtered),
            'engagement' => $this->analyzeEngagement($filtered),
            'recommendations' => $this->generateRecommendations($filtered),
        ];
    }

    // Helper methods
    private function storeFeedback(array $feedback): void
    {
        $feedbackList = Cache::get('feedback_list', []);
        $feedbackList[] = $feedback;
        Cache::put('feedback_list', $feedbackList, 86400 * 30); // 30 days
    }

    private function getFeedback(string $feedbackId): ?array
    {
        $feedbackList = Cache::get('feedback_list', []);
        
        foreach ($feedbackList as $feedback) {
            if ($feedback['id'] === $feedbackId) {
                return $feedback;
            }
        }
        
        return null;
    }

    private function updateFeedback(string $feedbackId, array $updatedFeedback): void
    {
        $feedbackList = Cache::get('feedback_list', []);
        
        foreach ($feedbackList as &$feedback) {
            if ($feedback['id'] === $feedbackId) {
                $feedback = $updatedFeedback;
                break;
            }
        }
        
        Cache::put('feedback_list', $feedbackList, 86400 * 30);
    }

    private function getAllFeedback(): array
    {
        return Cache::get('feedback_list', []);
    }

private function analyzeFeedbackWithAI(array $feedback): array
{
    // Simulated AI analysis
    return [
        'sentiment' => $this->analyzeSentiment($feedback['description']),
        'category_confidence' => rand(70, 95),
        'priority_suggestion' => $this->suggestPriority($feedback),
        'estimated_impact' => $this->estimateImpact($feedback),
        'keywords' => $this->extractKeywords($feedback['description']),
        'duplicate_probability' => $this->checkDuplicateProbability($feedback),
    ];
}

    private function analyzeSentiment(string $text): string
    {
        // Simple sentiment analysis
        $positiveWords = ['great', 'excellent', 'amazing', 'love', 'perfect', 'good', 'nice'];
        $negativeWords = ['bad', 'terrible', 'awful', 'hate', 'broken', 'issue', 'problem', 'bug'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            if (str_contains(strtolower($text), $word)) {
                $positiveCount++;
            }
        }
        
        foreach ($negativeWords as $word) {
            if (str_contains(strtolower($text), $word)) {
                $negativeCount++;
            }
        }
        
        if ($positiveCount > $negativeCount) return 'positive';
        if ($negativeCount > $positiveCount) return 'negative';
        return 'neutral';
    }

    private function suggestPriority(array $feedback): string
    {
        $description = strtolower($feedback['description']);
        
        if (str_contains($description, 'urgent') || str_contains($description, 'critical') || str_contains($description, 'broken')) {
            return 'high';
        }
        
        if (str_contains($description, 'issue') || str_contains($description, 'problem')) {
            return 'medium';
        }
        
        return 'low';
    }

    private function estimateImpact(array $feedback): string
    {
        $impactFactors = [
            'many users' => 'high',
            'everyone' => 'high',
            'team' => 'medium',
            'personal' => 'low',
        ];
        
        $description = strtolower($feedback['description']);
        
        foreach ($impactFactors as $factor => $impact) {
            if (str_contains($description, $factor)) {
                return $impact;
            }
        }
        
        return 'medium';
    }

    private function extractKeywords(string $text): array
    {
        // Simple keyword extraction
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were'];
        
        $keywords = array_diff($words, $stopWords);
        $keywordCounts = array_count_values($keywords);
        
        arsort($keywordCounts);
        
        return array_keys(array_slice($keywordCounts, 0, 5, true));
    }

    private function checkDuplicateProbability(array $feedback): float
    {
        // Check for similar existing feedback
        $allFeedback = $this->getAllFeedback();
        $similarCount = 0;
        
        foreach ($allFeedback as $existing) {
            if ($existing['id'] !== $feedback['id']) {
                $similarity = $this->calculateSimilarity($feedback, $existing);
                if ($similarity > 0.7) {
                    $similarCount++;
                }
            }
        }
        
        return min(1.0, $similarCount / max(1, count($allFeedback)));
    }

    private function calculateSimilarity(array $feedback1, array $feedback2): float
    {
        $title1 = strtolower($feedback1['title']);
        $title2 = strtolower($feedback2['title']);
        
        similar_text($title1, $title2, $percent);
        
        return $percent / 100;
    }

    private function calculateTrendingScore(array $feedback): float
    {
        $baseScore = $feedback['votes'];
        
        // Time decay factor (newer feedback gets boost)
        $daysOld = now()->diffInDays(Carbon::parse($feedback['created_at']));
        $timeFactor = max(0.1, 1 - ($daysOld / 30));
        
        // Engagement factor (comments, votes)
        $engagementFactor = 1 + (count($feedback['comments']) * 0.1) + ($feedback['votes'] * 0.05);
        
        return $baseScore * $timeFactor * $engagementFactor;
    }

    private function updateTrendingScore(string $feedbackId): void
    {
        $feedback = $this->getFeedback($feedbackId);
        if ($feedback) {
            $feedback['trending_score'] = $this->calculateTrendingScore($feedback);
            $this->updateFeedback($feedbackId, $feedback);
        }
    }

    private function notifyMentionedUsers(array $mentions, array $feedback, User $author): void
    {
        foreach ($mentions as $userId) {
            $notification = [
                'type' => 'feedback_mention',
                'feedback_id' => $feedback['id'],
                'feedback_title' => $feedback['title'],
                'mentioned_by' => $author->id,
                'created_at' => now()->toISOString(),
            ];
            
            Cache::push("notifications_{$userId}", $notification);
        }
    }

    private function applyFilters(array $feedback, array $filters): array
    {
        $filtered = $feedback;
        
        // Filter by type
        if (isset($filters['type'])) {
            $filtered = array_filter($filtered, function ($item) use ($filters) {
                return $item['type'] === $filters['type'];
            });
        }
        
        // Filter by category
        if (isset($filters['category'])) {
            $filtered = array_filter($filtered, function ($item) use ($filters) {
                return $item['category'] === $filters['category'];
            });
        }
        
        // Filter by status
        if (isset($filters['status'])) {
            $filtered = array_filter($filtered, function ($item) use ($filters) {
                return $item['status'] === $filters['status'];
            });
        }
        
        // Filter by tags
        if (isset($filters['tags'])) {
            $filtered = array_filter($filtered, function ($item) use ($filters) {
                return !empty(array_intersect($item['tags'], $filters['tags']));
            });
        }
        
        // Filter by date range
        if (isset($filters['date_from'])) {
            $from = Carbon::parse($filters['date_from']);
            $filtered = array_filter($filtered, function ($item) use ($from) {
                return Carbon::parse($item['created_at'])->greaterThanOrEqualTo($from);
            });
        }
        
        if (isset($filters['date_to'])) {
            $to = Carbon::parse($filters['date_to']);
            $filtered = array_filter($filtered, function ($item) use ($to) {
                return Carbon::parse($item['created_at'])->lessThanOrEqualTo($to);
            });
        }
        
        return array_values($filtered);
    }

    private function sortByRelevance(array $feedback, string $sort): array
    {
        switch ($sort) {
            case 'newest':
                usort($feedback, function ($a, $b) {
                    return Carbon::parse($b['created_at'])->timestamp - Carbon::parse($a['created_at'])->timestamp;
                });
                break;
            case 'oldest':
                usort($feedback, function ($a, $b) {
                    return Carbon::parse($a['created_at'])->timestamp - Carbon::parse($b['created_at'])->timestamp;
                });
                break;
            case 'votes':
                usort($feedback, function ($a, $b) {
                    return $b['votes'] - $a['votes'];
                });
                break;
            case 'trending':
            default:
                usort($feedback, function ($a, $b) {
                    $scoreA = $this->calculateTrendingScore($a);
                    $scoreB = $this->calculateTrendingScore($b);
                    return $scoreB - $scoreA;
                });
                break;
        }
        
        return $feedback;
    }

    private function getCategories(): array
    {
        $allFeedback = $this->getAllFeedback();
        $categories = array_unique(array_column($allFeedback, 'category'));
        
        return array_values($categories);
    }

    private function getPopularTags(): array
    {
        $allFeedback = $this->getAllFeedback();
        $allTags = [];
        
        foreach ($allFeedback as $feedback) {
            $allTags = array_merge($allTags, $feedback['tags']);
        }
        
        $tagCounts = array_count_values($allTags);
        arsort($tagCounts);
        
        return array_keys(array_slice($tagCounts, 0, 20, true));
    }

    private function findRelatedFeedback(array $feedback): array
    {
        $allFeedback = $this->getAllFeedback();
        $related = [];
        
        foreach ($allFeedback as $item) {
            if ($item['id'] !== $feedback['id']) {
                $similarity = $this->calculateSimilarity($feedback, $item);
                if ($similarity > 0.3) {
                    $related[] = array_merge($item, ['similarity' => $similarity]);
                }
            }
        }
        
        // Sort by similarity and return top 5
        usort($related, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($related, 0, 5);
    }

    private function findUsersWithSimilarFeedback(array $feedback): array
    {
        $allFeedback = $this->getAllFeedback();
        $similarUsers = [];
        
        foreach ($allFeedback as $item) {
            if ($item['id'] !== $feedback['id']) {
                $similarity = $this->calculateSimilarity($feedback, $item);
                if ($similarity > 0.5) {
                    $similarUsers[] = [
                        'user_id' => $item['user_id'],
                        'user_name' => $item['user_name'],
                        'feedback_id' => $item['id'],
                        'feedback_title' => $item['title'],
                        'similarity' => $similarity,
                    ];
                }
            }
        }
        
        return array_unique($similarUsers, SORT_REGULAR);
    }

    private function calculateEngagementMetrics(array $feedback): array
    {
        return [
            'total_votes' => $feedback['votes'],
            'total_comments' => count($feedback['comments']),
            'engagement_rate' => $feedback['votes'] + (count($feedback['comments']) * 2),
            'last_activity' => $feedback['updated_at'],
        ];
    }

    private function calculateUserFeedbackStats(array $feedback): array
    {
        $total = count($feedback);
        $votes = array_sum(array_column($feedback, 'votes'));
        $comments = array_sum(array_map('count', array_column($feedback, 'comments')));
        
        return [
            'total_feedback' => $total,
            'total_votes_received' => $votes,
            'total_comments_received' => $comments,
            'average_votes_per_feedback' => $total > 0 ? $votes / $total : 0,
            'most_voted_feedback' => $this->getMostVotedFeedback($feedback),
        ];
    }

    private function getMostVotedFeedback(array $feedback): ?array
    {
        if (empty($feedback)) return null;
        
        usort($feedback, function ($a, $b) {
            return $b['votes'] - $a['votes'];
        });
        
        return $feedback[0];
    }

    private function generateSummaryStats(array $feedback): array
    {
        $total = count($feedback);
        $votes = array_sum(array_column($feedback, 'votes'));
        $comments = array_sum(array_map('count', array_column($feedback, 'comments')));
        
        return [
            'total_feedback' => $total,
            'total_votes' => $votes,
            'total_comments' => $comments,
            'average_votes_per_feedback' => $total > 0 ? round($votes / $total, 2) : 0,
            'average_comments_per_feedback' => $total > 0 ? round($comments / $total, 2) : 0,
            'most_active_day' => $this->getMostActiveDay($feedback),
        ];
    }

    private function getMostActiveDay(array $feedback): string
    {
        $dayCounts = [];
        
        foreach ($feedback as $item) {
            $day = Carbon::parse($item['created_at'])->format('l');
            $dayCounts[$day] = ($dayCounts[$day] ?? 0) + 1;
        }
        
        return array_keys($dayCounts, max($dayCounts))[0] ?? 'Monday';
    }

    private function analyzeTrends(array $feedback): array
    {
        // Analyze feedback trends over time
        $monthlyData = [];
        
        foreach ($feedback as $item) {
            $month = Carbon::parse($item['created_at'])->format('Y-m');
            $monthlyData[$month] = ($monthlyData[$month] ?? 0) + 1;
        }
        
        ksort($monthlyData);
        
        return [
            'monthly_volume' => $monthlyData,
            'growth_rate' => $this->calculateGrowthRate($monthlyData),
            'peak_month' => array_keys($monthlyData, max($monthlyData))[0] ?? null,
        ];
    }

    private function calculateGrowthRate(array $monthlyData): float
    {
        if (count($monthlyData) < 2) return 0;
        
        $values = array_values($monthlyData);
        $first = $values[0];
        $last = end($values);
        
        if ($first == 0) return 0;
        
        return (($last - $first) / $first) * 100;
    }

    private function analyzeCategories(array $feedback): array
    {
        $categoryCounts = [];
        
        foreach ($feedback as $item) {
            $category = $item['category'];
            $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
        }
        
        arsort($categoryCounts);
        
        return [
            'distribution' => $categoryCounts,
            'most_common' => array_keys($categoryCounts)[0] ?? null,
            'diversity_score' => count($categoryCounts),
        ];
    }

    
    private function analyzeEngagement(array $feedback): array
    {
        $engagementData = [];
        
        foreach ($feedback as $item) {
            $engagement = $item['votes'] + (count($item['comments']) * 2);
            $engagementData[] = $engagement;
        }
        
        if (empty($engagementData)) {
            return [
                'average_engagement' => 0,
                'highest_engagement' => 0,
                'engagement_distribution' => [],
            ];
        }
        
        return [
            'average_engagement' => round(array_sum($engagementData) / count($engagementData), 2),
            'highest_engagement' => max($engagementData),
            'engagement_distribution' => $this->calculateEngagementDistribution($engagementData),
        ];
    }

    private function calculateEngagementDistribution(array $engagementData): array
    {
        $distribution = [
            'low' => 0,      // 0-2
            'medium' => 0,   // 3-5
            'high' => 0,     // 6-10
            'very_high' => 0, // 10+
        ];
        
        foreach ($engagementData as $engagement) {
            if ($engagement <= 2) $distribution['low']++;
            elseif ($engagement <= 5) $distribution['medium']++;
            elseif ($engagement <= 10) $distribution['high']++;
            else $distribution['very_high']++;
        }
        
        return $distribution;
    }

    private function generateRecommendations(array $feedback): array
    {
        $recommendations = [];
        
        // High-priority feedback
        $highPriority = array_filter($feedback, function ($item) {
            return $item['priority'] === 'high' && $item['status'] === 'new';
        });
        
        if (count($highPriority) > 5) {
            $recommendations[] = [
                'type' => 'action_needed',
                'message' => 'There are ' . count($highPriority) . ' high-priority feedback items requiring attention',
                'priority' => 'high',
            ];
        }
        
        // Popular categories
        $categoryAnalysis = $this->analyzeCategories($feedback);
        if ($categoryAnalysis['most_common']) {
            $recommendations[] = [
                'type' => 'focus_area',
                'message' => 'Consider focusing on ' . $categoryAnalysis['most_common'] . ' category improvements',
                'priority' => 'medium',
            ];
        }
        
        // Engagement opportunities
        $engagementAnalysis = $this->analyzeEngagement($feedback);
        if ($engagementAnalysis['average_engagement'] < 2) {
            $recommendations[] = [
                'type' => 'engagement',
                'message' => 'Consider improving feedback engagement through better communication',
                'priority' => 'low',
            ];
        }
        
        return $recommendations;
    }
}
