<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Banned = 'banned';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Expired => 'Kadaluarsa',
            self::Banned => 'Dibanned',
            self::Suspended => 'Disuspend',
        };
    }

    /**
     * Warna badge Tailwind untuk tampilan UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Expired => 'gray',
            self::Banned => 'red',
            self::Suspended => 'yellow',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Banned, self::Expired], true);
    }
}
