# Demo Data Setup Guide

This guide explains how to set up the Smart Project Hub demo environment with realistic data for demo day presentations.

## Overview

The demo seeder creates a complete, realistic environment showcasing all features of Smart Project Hub including:
- 8 users with African/global startup profiles
- 12 realistic projects across various sectors (fintech, IoT, education, healthcare, etc.)
- Tasks, budgets, and project data
- AI chat conversations focused on software, IoT, startups, and debugging
- Web3 badges and onchain activity
- Bounties and marketplace data
- Notifications and activity feeds

## Quick Start

### 1. Run the Demo Seeder

```bash
php artisan db:seed --class=DemoSeeder
```

This will populate your database with all demo data in a single command.

### 2. Login with Demo Users

The seeder creates 8 demo users. All have the password: `password`

| User | Email | Wallet | Badges |
|------|-------|--------|--------|
| Amara Okafor | amara.okafor@techhub.ng | 0x742d...e7d | Project Completed, Early Adopter |
| Kwame Mensah | kwame.mensah@innovate.gh | 0x8ba1...a72 | Top Innovator, Mentor |
| Fatima Zahra | fatima.zahra@startup.ke | 0xd8dA...045 | Task Master, Verified Builder |
| David Njoroge | david.njoroge@dev.co.ke | 0x1f98...984 | Project Completed |
| Aisha Bello | aisha.bello@tech.ng | 0xC02a...Cc2 | Early Adopter |
| Emeka Okonkwo | emeka.okonkwo@innovate.ng | 0x2260...999 | Project Completed, Top Innovator |
| Grace Mwangi | grace.mwangi@startup.ke | 0xA0b6...A48 | Mentor |
| Chinedu Okafor | chinedu.okafor@dev.ng | 0x6B17...0F | Task Master, Verified Builder |

## What Gets Seeded

### 1. Users (8 total)
- Realistic African startup founder profiles
- Wallet addresses for Web3 features
- Pre-earned badges based on achievements
- Diverse backgrounds (Nigeria, Ghana, Kenya)

### 2. Projects (12 total)
Realistic projects across sectors:
- **Fintech**: Payment Gateway, Mobile Banking, Digital Identity
- **IoT**: Smart Farming System
- **Education**: E-Learning Platform, Student Collaboration Hub
- **Healthcare**: Telemedicine App
- **Agritech**: Supply Chain Tracking
- **Energy**: Renewable Energy Microgrid
- **Marketplace**: Gig Economy Platform
- **Smart City**: Waste Management Optimization
- **AI**: Customer Service Chatbot

Each project includes:
- 8-15 tasks with realistic status
- Budget allocations across categories
- Web3 onchain data (transaction hashes, verification)
- Progress tracking and deadlines

### 3. AI Chat Conversations (5 topics)
Realistic conversations covering:
- **Software Architecture**: Microservices design for fintech
- **IoT Integration**: Low-cost sensor integration for farming
- **Startup Funding**: VC vs bootstrapping strategies
- **Student Debugging**: React memory leak troubleshooting
- **Budget Optimization**: AWS cost reduction strategies

Each conversation includes 3-4 message exchanges with realistic AI responses.

### 4. Web3 Features
- **Badges**: 6 badge types (Project Completed, Top Innovator, Mentor, Task Master, Early Adopter, Verified Builder)
- **Onchain Activity**: Project ownership proofs with Base Sepolia transaction hashes
- **Bounties**: 5 sample bounties with different statuses (open, assigned, completed)
- **Blueprints**: 1-3 blueprints per user with blockchain anchoring

### 5. Dashboard Data
The dashboard is populated with:
- **Weekly Signups**: 12 weeks of user registration data
- **Project Completion Trend**: 6 months of completion statistics
- **AI Chat Usage**: 7 days of session and message counts
- **Onchain Mints**: 12 weeks of project and badge minting data
- **Bounty Revenue**: 6 months of bounty completion revenue
- **Onchain Activity Feed**: Recent blockchain transactions with Base branding

## Dashboard Charts

### Weekly Signups Chart
Shows user registration trends over the last 12 weeks with realistic growth patterns.

### Project Completion Trend
Displays completed vs total projects over 6 months with completion rates.

### AI Chat Usage
Tracks chat sessions and messages over the last 7 days.

### Onchain Mints
Shows project ownership proofs and badge mints over 12 weeks on Base Sepolia.

### Bounty Revenue
Displays revenue from completed bounties over 6 months.

### Onchain Activity Feed
Real-time feed showing:
- Wallet addresses (shortened format: 0x742d...e7d)
- Transaction hashes (shortened format: 0x1234...5678)
- Activity types (project ownership, badge mints)
- Base Sepolia network badges
- Explorer links to BaseScan

## Routes Added

The demo setup adds these routes:

```php
// Dashboard with real data
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');

// Web3 features
Route::get('/web3/profile', [Web3Controller::class, 'profile'])->name('web3.profile');
Route::get('/web3/verification', [Web3Controller::class, 'verification'])->name('web3.verification');

// Blueprints
Route::get('/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
Route::post('/blueprints', [BlueprintController::class, 'store'])->name('blueprints.store');

// Bounties
Route::get('/bounties', function () {
    return view('pages.bounties');
})->name('bounties.index');
```

## Database Migrations Required

Run these migrations before seeding:

```bash
php artisan migrate
```

Required migrations:
- `2026_04_27_120000_add_web3_fields_to_projects_table` - Adds Web3 fields to projects
- `2026_04_27_120001_create_blueprints_table` - Creates blueprints table
- `2026_04_27_120002_create_bounties_table` - Creates bounties table

## Customizing Demo Data

### Modify User Data
Edit `database/seeders/DemoSeeder.php` in the `seedUsers()` method.

### Add More Projects
Edit the `$projectData` array in the `seedProjects()` method.

### Change AI Conversations
Modify the `$conversationTopics` array in the `seedChatSessions()` method.

### Adjust Dashboard Charts
Edit `app/Services/DashboardDataService.php` to change chart data generation logic.

## Resetting Demo Data

To clear all demo data and start fresh:

```bash
php artisan migrate:fresh --seed
```

This will:
1. Drop all tables
2. Re-run all migrations
3. Run the DemoSeeder

## Demo Day Checklist

Before your demo presentation:

- [ ] Run `php artisan migrate:fresh --seed`
- [ ] Test login with demo users
- [ ] Verify dashboard charts display correctly
- [ ] Check onchain activity feed shows Base branding
- [ ] Test Web3 profile page loads
- [ ] Verify AI chat conversations display
- [ ] Check blueprint upload functionality
- [ ] Test bounty marketplace page
- [ ] Ensure all routes are accessible
- [ ] Verify responsive design on different screen sizes

## Troubleshooting

### Seeder Fails
If the seeder fails, check:
- Database connection is configured in `.env`
- All migrations have been run
- User model has `badges` and `wallet_address` fillable fields

### Dashboard Charts Not Loading
If charts don't display:
- Check Chart.js is loaded in the layout
- Verify DashboardController is returning data
- Check browser console for JavaScript errors

### Web3 Features Not Working
If Web3 features fail:
- Verify wallet addresses are valid 42-character hex strings
- Check transaction hashes are 66-character hex strings
- Ensure Web3 routes are properly registered

## Performance Notes

The demo seeder creates:
- ~8 users
- ~12 projects
- ~100 tasks
- ~50 budget entries
- ~40 chat sessions
- ~120 chat messages
- ~16 badges
- ~5 bounties
- ~20 blueprints
- ~40 notifications

Total database operations: ~400, which should complete in under 5 seconds on most systems.

## Security Notes

**Important**: The demo users all have the password `password`. For production:
- Change all demo user passwords
- Remove or disable demo accounts
- Implement proper authentication policies
- Use environment-specific seeders

## Support

For issues with the demo setup:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database connection
3. Ensure all migrations are run
4. Check for conflicts with existing data

## Next Steps

After setting up demo data:
1. Deploy smart contracts to Base Sepolia
2. Update `.env` with contract addresses
3. Test wallet connection
4. Verify onchain transactions
5. Prepare demo script highlighting key features
