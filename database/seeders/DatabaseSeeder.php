<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\ApprovalLevel;
use App\Models\Role;
use App\Models\Group;
use App\Models\Etalase;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        $roles = ['vendor', 'company','user_approval'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Groups
        $groups = ['Electronics', 'Clothing', 'Furniture'];
        foreach ($groups as $groupName) {
            Group::create(['name' => $groupName]);
        }

        // Categories
        $categories = ['Books', 'Electronics', 'Clothing', 'Furniture'];
        foreach ($categories as $categoryName) {
            Category::create(['name' => $categoryName]);
        }

        // Currencies
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => 'USD'],
            ['name' => 'Euro', 'symbol' => 'EUR'],
            ['name' => 'British Pound', 'symbol' => 'GBP'],
            ['name' => 'Japanese Yen', 'symbol' => 'JPY'],
            ['name' => 'Australian Dollar', 'symbol' => 'AUD'],
            ['name' => 'Canadian Dollar', 'symbol' => 'CAD'],
            ['name' => 'Swiss Franc', 'symbol' => 'CHF'],
            ['name' => 'Chinese Yuan', 'symbol' => 'CNY'],
            ['name' => 'Swedish Krona', 'symbol' => 'SEK'],
            ['name' => 'Indonesian Rupiah', 'symbol' => 'IDR'],
        ];
        foreach ($currencies as $currency) {
            Currency::create($currency);
        }

        // Etalase
        $etalases = ['Best Seller', 'New Arrival', 'Most Popular'];
        foreach ($etalases as $etalaseName) {
            Etalase::create(['name' => $etalaseName]);
        }

    }
}
