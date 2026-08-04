<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');
        if ($adminPassword) {
            User::updateOrCreate(['email' => env('ADMIN_EMAIL', 'admin@mahamilon.local')], [
                'name' => 'Mahamilon Super Admin', 'password' => Hash::make($adminPassword),
                'role' => 'super_admin', 'status' => 'active',
            ]);
        }
        foreach ([
            ['SAVINGS','Savings Account','savings','monthly',8,500,'Minimum one share of ₹500 per month'],
            ['RD-DAILY','Daily Deposit Account','rd','daily',9,100,'Daily recurring deposit'],
            ['RD-MONTHLY','Monthly Deposit Account','rd','monthly',8,500,'Minimum one ₹500 share per month'],
            ['FD-CLASSIC','Fixed Deposit Account','fd','fixed',8,20000,'Minimum fixed deposit ₹20,000'],
        ] as [$code,$name,$type,$frequency,$rate,$minimum,$description]) DB::table('schemes')->updateOrInsert(['code'=>$code],['name'=>$name,'type'=>$type,'deposit_frequency'=>$frequency,'annual_rate'=>$rate,'minimum_amount'=>$minimum,'description'=>$description,'active'=>true,'created_at'=>now(),'updated_at'=>now()]);
        DB::table('society_settings')->updateOrInsert(['key'=>'upi_id'],['value'=>env('SOCIETY_UPI_ID','mahamilon@upi'),'created_at'=>now(),'updated_at'=>now()]);
        DB::table('society_settings')->updateOrInsert(['key'=>'payee_name'],['value'=>'Mahamilon Society','created_at'=>now(),'updated_at'=>now()]);
    }
}
