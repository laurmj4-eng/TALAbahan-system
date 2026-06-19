<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds per-model daily quota columns to the users table.
 *
 * Previously, a single prompt_count tracked combined usage with a tiered split
 * (first 25 = Gemini, next 25 = OpenRouter). This migration adds three
 * independent counters so each model (gemini, gemma, gpt) has its own 25/day
 * budget. The legacy prompt_count column is kept for backward compatibility.
 */
class AddPerModelQuotaColumns extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'gemini_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'prompt_count',
            ],
            'gemma_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'gemini_count',
            ],
            'gpt_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'gemma_count',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'gemini_count');
        $this->forge->dropColumn('users', 'gemma_count');
        $this->forge->dropColumn('users', 'gpt_count');
    }
}
