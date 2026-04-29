<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Budget;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Demo Data Seeding...');

        // Seed Users
        $this->seedUsers();

        // Seed Projects
        $this->seedProjects();

        // Seed Tasks
        $this->seedTasks();

        // Seed Budgets
        $this->seedBudgets();

        // Seed Chat Sessions
        $this->seedChatSessions();

        // Seed Badges
        $this->seedBadges();

        // Seed Bounties
        $this->seedBounties();

        // Seed Notifications
        $this->seedNotifications();

        // Seed Blueprints
        $this->seedBlueprints();

        $this->command->info('Demo Data Seeding Complete!');
    }

    private function seedUsers(): void
    {
        $users = [
            [
                'name' => 'Amara Okafor',
                'email' => 'amara.okafor@techhub.ng',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0x742d35Cc6634C0532925a3b844Bc9e7595f8bE7d',
                'badges' => json_encode([
                    ['type' => 'PROJECT_COMPLETED', 'awarded_at' => now()->subDays(30)->toISOString()],
                    ['type' => 'EARLY_ADOPTER', 'awarded_at' => now()->subDays(60)->toISOString()],
                ]),
            ],
            [
                'name' => 'Kwame Mensah',
                'email' => 'kwame.mensah@innovate.gh',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0x8ba1f109551bD432803012645Ac136ddd64DBA72',
                'badges' => json_encode([
                    ['type' => 'TOP_INNOVATOR', 'awarded_at' => now()->subDays(15)->toISOString()],
                    ['type' => 'MENTOR', 'awarded_at' => now()->subDays(45)->toISOString()],
                ]),
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@startup.ke',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0xd8dA6BF26964aF9D7eEd9e03E53415D37aA96045',
                'badges' => json_encode([
                    ['type' => 'TASKS_100', 'awarded_at' => now()->subDays(20)->toISOString()],
                    ['type' => 'VERIFIED_BUILDER', 'awarded_at' => now()->subDays(10)->toISOString()],
                ]),
            ],
            [
                'name' => 'David Njoroge',
                'email' => 'david.njoroge@dev.co.ke',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0x1f9840a85d5aF5bf1D1762F925BDADdC4201F984',
                'badges' => json_encode([
                    ['type' => 'PROJECT_COMPLETED', 'awarded_at' => now()->subDays(5)->toISOString()],
                ]),
            ],
            [
                'name' => 'Aisha Bello',
                'email' => 'aisha.bello@tech.ng',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0xC02aaA39b223FE8D0A0e5C4F27eAD9083C756Cc2',
                'badges' => json_encode([
                    ['type' => 'EARLY_ADOPTER', 'awarded_at' => now()->subDays(90)->toISOString()],
                ]),
            ],
            [
                'name' => 'Emeka Okonkwo',
                'email' => 'emeka.okonkwo@innovate.ng',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0x2260FAC5E5542a773Aa44fBCfeDf7C193bc2C599',
                'badges' => json_encode([
                    ['type' => 'PROJECT_COMPLETED', 'awarded_at' => now()->subDays(25)->toISOString()],
                    ['type' => 'TOP_INNOVATOR', 'awarded_at' => now()->subDays(35)->toISOString()],
                ]),
            ],
            [
                'name' => 'Grace Mwangi',
                'email' => 'grace.mwangi@startup.ke',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0xA0b86991c6218b36c1d19D4a2e9Eb0cE3606eB48',
                'badges' => json_encode([
                    ['type' => 'MENTOR', 'awarded_at' => now()->subDays(50)->toISOString()],
                ]),
            ],
            [
                'name' => 'Chinedu Okafor',
                'email' => 'chinedu.okafor@dev.ng',
                'password' => Hash::make('password'),
                'role' => 'user',
                'wallet_address' => '0x6B175474E89094C44Da98b954EedeAC495271d0F',
                'badges' => json_encode([
                    ['type' => 'TASKS_100', 'awarded_at' => now()->subDays(12)->toISOString()],
                    ['type' => 'VERIFIED_BUILDER', 'awarded_at' => now()->subDays(8)->toISOString()],
                ]),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Seeded 8 users');
    }

    private function seedProjects(): void
    {
        $users = User::all();
        $projectData = [
            [
                'title' => 'Fintech Payment Gateway for African Markets',
                'description' => 'Building a seamless payment gateway that integrates with M-Pesa, MTN Mobile Money, and traditional banks to enable cross-border transactions across Africa.',
                'category' => 'fintech',
                'status' => 'completed',
                'progress' => 100,
                'estimated_budget' => 50000.00,
                'deadline' => now()->subDays(15),
            ],
            [
                'title' => 'IoT Smart Farming System',
                'description' => 'Developing an IoT platform for smallholder farmers in Kenya to monitor soil moisture, weather patterns, and crop health using affordable sensors.',
                'category' => 'iot',
                'status' => 'in_progress',
                'progress' => 75,
                'estimated_budget' => 35000.00,
                'deadline' => now()->addDays(30),
            ],
            [
                'title' => 'E-Learning Platform for Rural Schools',
                'description' => 'Creating an offline-first e-learning platform that works with limited connectivity, featuring curriculum-aligned content for Nigerian primary schools.',
                'category' => 'education',
                'status' => 'in_progress',
                'progress' => 60,
                'estimated_budget' => 28000.00,
                'deadline' => now()->addDays(45),
            ],
            [
                'title' => 'Healthcare Telemedicine App',
                'description' => 'Building a telemedicine application connecting patients in remote areas with doctors in urban centers, with AI-powered triage and appointment scheduling.',
                'category' => 'healthcare',
                'status' => 'completed',
                'progress' => 100,
                'estimated_budget' => 75000.00,
                'deadline' => now()->subDays(30),
            ],
            [
                'title' => 'Supply Chain Tracking for Agricultural Products',
                'description' => 'Blockchain-based supply chain tracking system for agricultural products from farm to market, ensuring quality and fair pricing for farmers.',
                'category' => 'agritech',
                'status' => 'planning',
                'progress' => 25,
                'estimated_budget' => 42000.00,
                'deadline' => now()->addDays(60),
            ],
            [
                'title' => 'Renewable Energy Microgrid Management',
                'description' => 'Software platform for managing solar microgrids in off-grid communities, optimizing energy distribution and billing.',
                'category' => 'energy',
                'status' => 'in_progress',
                'progress' => 45,
                'estimated_budget' => 65000.00,
                'deadline' => now()->addDays(90),
            ],
            [
                'title' => 'Gig Economy Platform for Skilled Labor',
                'description' => 'Marketplace connecting skilled workers (plumbers, electricians, carpenters) with customers in urban areas, with reputation system and mobile payments.',
                'category' => 'marketplace',
                'status' => 'completed',
                'progress' => 100,
                'estimated_budget' => 55000.00,
                'deadline' => now()->subDays(10),
            ],
            [
                'title' => 'Digital Identity Verification System',
                'description' => 'Biometric digital identity system compatible with national ID systems, enabling secure access to government and financial services.',
                'category' => 'fintech',
                'status' => 'in_progress',
                'progress' => 55,
                'estimated_budget' => 80000.00,
                'deadline' => now()->addDays(75),
            ],
            [
                'title' => 'Waste Management Optimization Platform',
                'description' => 'AI-powered platform for optimizing waste collection routes in cities, reducing fuel consumption and improving recycling rates.',
                'category' => 'smartcity',
                'status' => 'planning',
                'progress' => 15,
                'estimated_budget' => 32000.00,
                'deadline' => now()->addDays(120),
            ],
            [
                'title' => 'Student Project Collaboration Hub',
                'description' => 'Platform for university students to collaborate on capstone projects, with version control, task management, and faculty supervision tools.',
                'category' => 'education',
                'status' => 'completed',
                'progress' => 100,
                'estimated_budget' => 18000.00,
                'deadline' => now()->subDays(5),
            ],
            [
                'title' => 'Mobile Banking for Unbanked Populations',
                'description' => 'Lightweight mobile banking app designed for feature phones, enabling basic financial services for unbanked populations in rural areas.',
                'category' => 'fintech',
                'status' => 'in_progress',
                'progress' => 70,
                'estimated_budget' => 45000.00,
                'deadline' => now()->addDays(50),
            ],
            [
                'title' => 'AI-Powered Customer Service Chatbot',
                'description' => 'Multilingual AI chatbot for customer service, supporting major African languages and integrating with popular messaging platforms.',
                'category' => 'ai',
                'status' => 'planning',
                'progress' => 20,
                'estimated_budget' => 38000.00,
                'deadline' => now()->addDays(80),
            ],
        ];

        foreach ($projectData as $data) {
            $user = $users->random();
            $project = Project::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                ],
                array_merge($data, ['user_id' => $user->id])
            );

            // Add Web3 data for some projects
            if (in_array($data['status'], ['completed', 'in_progress']) && rand(0, 1)) {
                $project->update([
                    'project_hash' => '0x' . Str::random(64),
                    'wallet_address' => $user->wallet_address,
                    'transaction_hash' => '0x' . Str::random(64),
                    'blockchain_verified_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('Seeded 12 projects');
    }

    private function seedTasks(): void
    {
        $projects = Project::all();
        $taskTemplates = [
            ['title' => 'Design system architecture', 'status' => 'completed'],
            ['title' => 'Set up development environment', 'status' => 'completed'],
            ['title' => 'Create database schema', 'status' => 'completed'],
            ['title' => 'Implement user authentication', 'status' => 'completed'],
            ['title' => 'Build REST API endpoints', 'status' => 'in_progress'],
            ['title' => 'Design UI/UX mockups', 'status' => 'completed'],
            ['title' => 'Implement frontend components', 'status' => 'in_progress'],
            ['title' => 'Integrate payment gateway', 'status' => 'pending'],
            ['title' => 'Write unit tests', 'status' => 'pending'],
            ['title' => 'Deploy to staging environment', 'status' => 'pending'],
            ['title' => 'Performance optimization', 'status' => 'pending'],
            ['title' => 'Security audit', 'status' => 'pending'],
            ['title' => 'Documentation', 'status' => 'pending'],
            ['title' => 'User acceptance testing', 'status' => 'pending'],
            ['title' => 'Production deployment', 'status' => 'pending'],
        ];

        foreach ($projects as $project) {
            $taskCount = rand(8, 15);
            $shuffledTasks = collect($taskTemplates)->shuffle()->take($taskCount);
            
            foreach ($shuffledTasks as $taskData) {
                Task::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'title' => $taskData['title'],
                    ],
                    array_merge($taskData, [
                        'user_id' => $project->user_id,
                        'project_id' => $project->id,
                        'description' => 'Implementation of ' . $taskData['title'] . ' for ' . $project->title,
                        'due_date' => $project->deadline,
                        'completed_at' => $taskData['status'] === 'completed' ? now()->subDays(rand(1, 30)) : null,
                    ])
                );
            }
        }

        $this->command->info('Seeded tasks for all projects');
    }

    private function seedBudgets(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            if ($project->estimated_budget) {
                $budgetCategories = ['Development', 'Design', 'Marketing', 'Infrastructure', 'Legal'];
                $totalBudget = $project->estimated_budget;
                
                foreach ($budgetCategories as $category) {
                    $amount = $totalBudget * (rand(10, 30) / 100);
                    
                    Budget::firstOrCreate(
                        [
                            'project_id' => $project->id,
                            'title' => $category . ' Budget',
                        ],
                        [
                            'user_id' => $project->user_id,
                            'project_id' => $project->id,
                            'title' => $category . ' Budget',
                            'description' => 'Budget allocation for ' . $category . ' in ' . $project->title,
                            'amount' => $amount,
                            'type' => 'expense',
                            'transaction_date' => now()->subDays(rand(1, 30)),
                        ]
                    );
                }
            }
        }

        $this->command->info('Seeded budgets for all projects');
    }

    private function seedChatSessions(): void
    {
        $users = User::all();
        $conversationTopics = [
            ['topic' => 'Software Architecture', 'messages' => [
                ['role' => 'user', 'content' => 'I need help designing a microservices architecture for my fintech app. What are the key considerations?'],
                ['role' => 'assistant', 'content' => 'For a fintech microservices architecture, consider: 1) Service boundaries around business capabilities (payments, user management, transactions), 2) API Gateway for routing, 3) Event-driven communication for async operations, 4) Strong consistency for financial operations, 5) Circuit breakers for resilience. Would you like me to elaborate on any of these?'],
                ['role' => 'user', 'content' => 'Yes, especially about handling transactions across services. How do I ensure data consistency?'],
                ['role' => 'assistant', 'content' => 'For distributed transactions in fintech, I recommend the Saga pattern. Implement compensating transactions for rollback scenarios. For critical operations, use 2-phase commit with a transaction coordinator. Also consider event sourcing for audit trails. Would you like a code example of the Saga pattern?'],
            ]],
            ['topic' => 'IoT Sensor Integration', 'messages' => [
                ['role' => 'user', 'content' => 'How do I integrate low-cost IoT sensors with my farming platform? The sensors have limited connectivity.'],
                ['role' => 'assistant', 'content' => 'For low-connectivity IoT sensors, implement: 1) Edge computing to process data locally, 2) Store-and-forward mechanism for data transmission, 3) Compression algorithms to reduce bandwidth, 4) LoRaWAN or NB-IoT for long-range communication, 5) MQTT with QoS 1 for message delivery. What type of sensors are you using?'],
                ['role' => 'user', 'content' => 'Soil moisture and temperature sensors. They need to run on batteries for months.'],
                ['role' => 'assistant', 'content' => 'For battery-powered soil sensors: Use deep sleep modes, wake only on threshold changes or scheduled intervals. Implement adaptive sampling rates. Consider energy harvesting (solar) for extended operation. Use lightweight protocols like CoAP instead of HTTP. I can help design the power management circuit.'],
            ]],
            ['topic' => 'Startup Funding Strategy', 'messages' => [
                ['role' => 'user', 'content' => 'My African startup is ready for funding. Should I go with VCs or bootstrapping?'],
                ['role' => 'assistant', 'content' => 'Consider your growth stage and goals. Bootstrapping gives you full control but slower growth. VCs provide capital and networks but require equity dilution. For African startups, also consider: 1) Local angel investors and accelerators (like Techstars, YC), 2) Government grants and innovation funds, 3) Corporate venture programs, 4) Crowdfunding. What\'s your current revenue and team size?'],
                ['role' => 'user', 'content' => 'We have $50k MRR and a team of 8. Looking to expand to 3 more countries.'],
                ['role' => 'assistant', 'content' => 'With $50k MRR and 8-person team, you\'re in a strong position. For expansion, consider a Series A round of $2-5M. Before pitching: 1) Document your unit economics and CAC, 2) Show retention metrics (>80% is good), 3) Have a clear 18-month roadmap, 4) Identify VCs with Africa focus (TLcom Capital, CRE Venture Capital). I can help prepare your pitch deck.'],
            ]],
            ['topic' => 'Student Project Debugging', 'messages' => [
                ['role' => 'user', 'content' => 'My React app has a memory leak. Components are not unmounting properly. How do I debug this?'],
                ['role' => 'assistant', 'content' => 'Memory leaks in React often come from: 1) Event listeners not cleaned up in useEffect, 2) Intervals/timeouts not cleared, 3) Closures holding references, 4) Large objects in state. Use React DevTools Profiler to identify problematic components. Check for missing cleanup functions in useEffect. Would you like me to review your useEffect hooks?'],
                ['role' => 'user', 'content' => 'Yes, here\'s my component code. [pastes code]'],
                ['role' => 'assistant', 'content' => 'I see the issue - you have a setInterval in useEffect but no cleanup function. Add this: `return () => clearInterval(intervalId);` Also, consider using requestAnimationFrame instead of setInterval for better performance. I\'ll show you the corrected code.'],
            ]],
            ['topic' => 'Budget Optimization', 'messages' => [
                ['role' => 'user', 'content' => 'My cloud costs are eating up 40% of our budget. How can I reduce AWS expenses?'],
                ['role' => 'assistant', 'content' => 'To reduce AWS costs: 1) Use Reserved Instances for steady workloads (30-50% savings), 2) Implement auto-scaling to scale down during off-hours, 3) Use Spot Instances for non-critical jobs (up to 90% savings), 4) Optimize storage tiers (S3 Intelligent Tiering), 5) Review and delete unused resources. What\'s your biggest cost driver?'],
                ['role' => 'user', 'content' => 'EC2 instances are the main cost. We run web servers and batch processing jobs.'],
                ['role' => 'assistant', 'content' => 'For EC2 optimization: 1) Move web servers to ECS/Fargate for better utilization, 2) Use Lambda for batch jobs instead of always-on instances, 3) Implement instance scheduling for dev environments, 4) Right-size instances using AWS Compute Optimizer, 5) Consider Graviton instances for up to 40% cost savings. I can help you implement these changes.'],
            ]],
        ];

        foreach ($users as $user) {
            $sessionCount = rand(2, 5);
            $shuffledTopics = collect($conversationTopics)->shuffle()->take($sessionCount);
            
            foreach ($shuffledTopics as $conversation) {
                $session = ChatSession::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => $conversation['topic'],
                    ],
                    [
                        'user_id' => $user->id,
                        'title' => $conversation['topic'],
                    ]
                );

                foreach ($conversation['messages'] as $index => $messageData) {
                    ChatMessage::firstOrCreate(
                        [
                            'chat_session_id' => $session->id,
                            'message' => $messageData['content'],
                        ],
                        [
                            'chat_session_id' => $session->id,
                            'sender' => $messageData['role'],
                            'message' => $messageData['content'],
                            'created_at' => now()->subHours(count($conversation['messages']) - $index),
                        ]
                    );
                }
            }
        }

        $this->command->info('Seeded chat sessions for all users');
    }

    private function seedBadges(): void
    {
        $users = User::all();
        $badgeTypes = ['PROJECT_COMPLETED', 'TOP_INNOVATOR', 'MENTOR', 'TASKS_100', 'EARLY_ADOPTER', 'VERIFIED_BUILDER'];

        foreach ($users as $user) {
            $existingBadges = json_decode($user->badges ?? '[]', true);
            $badgeTypesArray = array_column($existingBadges, 'type');
            
            // Ensure user has at least 2 badges
            $neededBadges = 2 - count($badgeTypesArray);
            if ($neededBadges > 0) {
                $availableBadges = array_diff($badgeTypes, $badgeTypesArray);
                $newBadges = array_slice($availableBadges, 0, $neededBadges);
                
                foreach ($newBadges as $badgeType) {
                    $existingBadges[] = [
                        'type' => $badgeType,
                        'awarded_at' => now()->subDays(rand(1, 60))->toISOString(),
                    ];
                }
                
                $user->update(['badges' => json_encode($existingBadges)]);
            }
        }

        $this->command->info('Updated badges for all users');
    }

    private function seedBounties(): void
    {
        $users = User::all();
        $bountyData = [
            [
                'title' => 'Build React Component Library',
                'description' => 'Create a reusable React component library with 20+ components, TypeScript support, and Storybook documentation.',
                'reward' => 500,
                'status' => 'open',
            ],
            [
                'title' => 'Smart Contract Audit',
                'description' => 'Audit a DeFi smart contract for security vulnerabilities and gas optimization opportunities.',
                'reward' => 1000,
                'status' => 'assigned',
            ],
            [
                'title' => 'UI/UX Design for Dashboard',
                'description' => 'Design a modern, responsive dashboard UI with dark mode support and accessibility features.',
                'reward' => 300,
                'status' => 'open',
            ],
            [
                'title' => 'API Integration with Payment Gateway',
                'description' => 'Integrate Flutterwave API for payment processing with proper error handling and webhook support.',
                'reward' => 750,
                'status' => 'completed',
            ],
            [
                'title' => 'Mobile App Performance Optimization',
                'description' => 'Optimize React Native app performance, reduce bundle size, and improve load times.',
                'reward' => 400,
                'status' => 'open',
            ],
        ];

        foreach ($bountyData as $data) {
            $creator = $users->random();
            $bounty = \App\Models\Bounty::firstOrCreate(
                [
                    'title' => $data['title'],
                ],
                array_merge($data, [
                    'creator_id' => $creator->id,
                    'winner_id' => $data['status'] === 'completed' ? $users->where('id', '!=', $creator->id)->random()->id : null,
                    'deadline' => now()->addDays(rand(7, 30)),
                ])
            );
        }

        $this->command->info('Seeded 5 bounties');
    }

    private function seedNotifications(): void
    {
        // Skip notifications seeding if table doesn't exist or has different schema
        $this->command->info('Skipped notifications (table structure mismatch)');
    }

    private function seedBlueprints(): void
    {
        $users = User::all();
        $projects = Project::all();
        
        foreach ($users as $user) {
            $blueprintCount = rand(1, 3);
            
            for ($i = 0; $i < $blueprintCount; $i++) {
                $project = $projects->random();
                
                Blueprint::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'title' => 'Blueprint ' . ($i + 1) . ' for ' . $project->title,
                    ],
                    [
                        'user_id' => $user->id,
                        'project_id' => $project->id,
                        'title' => 'Blueprint ' . ($i + 1) . ' for ' . $project->title,
                        'description' => 'Technical blueprint and architecture document for ' . $project->title,
                        'file_path' => 'blueprints/' . Str::random(40) . '.pdf',
                        'file_name' => 'blueprint_' . Str::random(10) . '.pdf',
                        'file_type' => 'application/pdf',
                        'file_size' => rand(100000, 5000000),
                        'file_hash' => hash('sha256', Str::random(100)),
                        'blockchain_hash' => rand(0, 1) ? '0x' . Str::random(64) : null,
                        'transaction_hash' => rand(0, 1) ? '0x' . Str::random(64) : null,
                        'blockchain_anchored_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                        'is_verified' => rand(0, 1),
                    ]
                );
            }
        }

        $this->command->info('Seeded blueprints for all users');
    }
}
