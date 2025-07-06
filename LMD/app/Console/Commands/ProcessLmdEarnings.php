<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class ProcessLmdEarnings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'lmd:process-earnings';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */


public function handle()
{
    $settings = DB::table('lmdsettings')->first();

    if (!$settings) {
        $this->warn('⚠️ No LMD settings found.');
        return;
    }

    $insertCount = DB::insert(
        "
        INSERT INTO lmd_earnings (
            suborder_id,
            order_total,
            lmd_charge_percentage,
            lmd_earning_amount,
            tax_percentage,
            tax_amount,
            created_at,
            updated_at
        )
        SELECT
            s.id,
            s.total_amount,
            ?,
            ROUND((? / 100) * s.total_amount, 2),
            ?,
            ROUND((? / 100) * ((? / 100) * s.total_amount), 2),
            NOW(),
            NOW()
        FROM suborders s
        WHERE s.payment_status = 'confirmed_by_vendor'
          AND NOT EXISTS (
              SELECT 1 FROM lmd_earnings le WHERE le.suborder_id = s.id
          )
        ",
        [
            $settings->order_charge,
            $settings->order_charge,
            $settings->tax_percentage,
            $settings->tax_percentage,
            $settings->order_charge
        ]
    );

    $this->info("✅ LMD earnings processed. New entries added.");
    \Log::info('✅ LMD earnings task ran at: ' . now());
}


}
