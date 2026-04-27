<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Project reminders
            Notification::create([
                'user_id' => $user->id,
                'type' => 'project_reminders',
                'title' => 'Project Deadline Approaching',
                'message' => 'Your project "Website Redesign" is due in 3 days. Make sure to complete all tasks.',
                'read' => false,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'project_reminders',
                'title' => 'Weekly Project Review',
                'message' => 'Don\'t forget your weekly project review meeting scheduled for tomorrow at 2 PM.',
                'read' => true,
            ]);

            // Task deadlines
            Notification::create([
                'user_id' => $user->id,
                'type' => 'task_deadlines',
                'title' => 'Task Due Today',
                'message' => 'The task "Implement user authentication" is due today. Please complete it ASAP.',
                'read' => false,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'task_deadlines',
                'title' => 'Overdue Task',
                'message' => 'Task "Database optimization" is now overdue. Please prioritize this task.',
                'read' => false,
            ]);

            // AI updates
            Notification::create([
                'user_id' => $user->id,
                'type' => 'ai_updates',
                'title' => 'New AI Features Available',
                'message' => 'We\'ve added new AI-powered task suggestions. Try them out in your project dashboard!',
                'read' => false,
            ]);

            // System alerts
            Notification::create([
                'user_id' => $user->id,
                'type' => 'system_alerts',
                'title' => 'System Maintenance',
                'message' => 'Scheduled maintenance will occur tonight from 2 AM to 4 AM. Service may be temporarily unavailable.',
                'read' => true,
            ]);

            Notification::create([
                'user_id' => $user->id,
                'type' => 'system_alerts',
                'title' => 'Security Update',
                'message' => 'A security update has been applied to your account. Please review your security settings.',
                'read' => false,
            ]);
        }
    }
}
