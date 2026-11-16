<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $builtins = [
            [
                'name' => 'Facebook Page',
                'slug' => 'facebook',
                'short_code' => 'FB',
                'color' => '#1877F2',
                'description' => 'Meta Facebook Messenger and Page conversations',
                'is_builtin' => true,
                'is_active' => true,
            ],
            [
                'name' => 'WhatsApp Cloud',
                'slug' => 'whatsapp',
                'short_code' => 'WA',
                'color' => '#25D366',
                'description' => 'Meta WhatsApp Business Cloud API messaging',
                'is_builtin' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Telegram Bot',
                'slug' => 'telegram',
                'short_code' => 'TG',
                'color' => '#229ED9',
                'description' => 'Telegram Official Bot API by BotFather',
                'is_builtin' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Instagram DM',
                'slug' => 'instagram',
                'short_code' => 'IG',
                'color' => '#E1306C',
                'description' => 'Instagram Direct Messaging for Business Accounts',
                'is_builtin' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Web Widget',
                'slug' => 'webchat',
                'short_code' => 'WEB',
                'color' => '#6366F1',
                'description' => 'Embeddable website live chat widget for visitors',
                'is_builtin' => true,
                'is_active' => true,
            ],
        ];

        foreach ($builtins as $p) {
            Platform::updateOrCreate(
                ['slug' => $p['slug'], 'tenant_id' => null],
                $p
            );
        }
    }
}
