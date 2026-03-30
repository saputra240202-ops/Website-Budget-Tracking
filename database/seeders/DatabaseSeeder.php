<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat user demo
        $user = User::create([
            'name'     => 'Alex Johnson',
            'email'    => 'alex@example.com',
            'password' => Hash::make('password'),
            'currency' => 'IDR (Rp)',
            'timezone' => 'Pacific Time (PT)',
        ]);

        // Buat kategori income
        $incomeCategories = [
            ['name' => 'Salary',      'type' => 'income', 'color' => '#10b981'],
            ['name' => 'Freelance',   'type' => 'income', 'color' => '#3b82f6'],
            ['name' => 'Investments', 'type' => 'income', 'color' => '#8b5cf6'],
        ];

        // Buat kategori expense
        $expenseCategories = [
            ['name' => 'Food & Dining',    'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Transport',        'type' => 'expense', 'color' => '#3b82f6'],
            ['name' => 'Shopping',         'type' => 'expense', 'color' => '#ec4899'],
            ['name' => 'Bills & Utilities','type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Entertainment',    'type' => 'expense', 'color' => '#8b5cf6'],
        ];

        $allCategories = [];
        foreach (array_merge($incomeCategories, $expenseCategories) as $cat) {
            $allCategories[$cat['name']] = Category::create([
                'user_id' => $user->id,
                'name'    => $cat['name'],
                'type'    => $cat['type'],
                'color'   => $cat['color'],
            ]);
        }

        // Buat transaksi dummy
        $transactions = [
            ['category' => 'Salary',          'description' => 'October Salary',  'amount' => 3000.00, 'type' => 'income',  'date' => '2023-10-24'],
            ['category' => 'Food & Dining',    'description' => 'Whole Foods',     'amount' => 120.50,  'type' => 'expense', 'date' => '2023-10-23'],
            ['category' => 'Transport',        'description' => 'Uber Ride',       'amount' => 24.00,   'type' => 'expense', 'date' => '2023-10-22'],
            ['category' => 'Shopping',         'description' => 'Amazon',          'amount' => 89.99,   'type' => 'expense', 'date' => '2023-10-21'],
            ['category' => 'Freelance',        'description' => 'Logo Design',     'amount' => 850.00,  'type' => 'income',  'date' => '2023-10-20'],
            ['category' => 'Bills & Utilities','description' => 'Electricity Bill','amount' => 75.00,   'type' => 'expense', 'date' => '2023-10-19'],
            ['category' => 'Entertainment',    'description' => 'Netflix',         'amount' => 15.99,   'type' => 'expense', 'date' => '2023-10-18'],
            ['category' => 'Investments',      'description' => 'Dividend',        'amount' => 200.00,  'type' => 'income',  'date' => '2023-10-17'],
        ];

        foreach ($transactions as $tx) {
            Transaction::create([
                'user_id'     => $user->id,
                'category_id' => $allCategories[$tx['category']]->id,
                'description' => $tx['description'],
                'amount'      => $tx['amount'],
                'type'        => $tx['type'],
                'date'        => $tx['date'],
            ]);
        }

        // Buat budget dummy
        $budgets = [
            ['category' => 'Food & Dining',    'limit' => 1000.00, 'used' => 700.00],
            ['category' => 'Transport',        'limit' => 500.00,  'used' => 450.00],
            ['category' => 'Shopping',         'limit' => 800.00,  'used' => 850.00],
            ['category' => 'Bills & Utilities','limit' => 400.00,  'used' => 200.00],
            ['category' => 'Entertainment',    'limit' => 300.00,  'used' => 250.00],
        ];

        foreach ($budgets as $b) {
            Budget::create([
                'user_id'      => $user->id,
                'category_id'  => $allCategories[$b['category']]->id,
                'limit_amount' => $b['limit'],
                'used_amount'  => $b['used'],
                'period'       => 'monthly',
            ]);
        }
    }
}