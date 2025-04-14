<?php

namespace App\Doctrine\Type;

use App\Enum\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class RoleType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value instanceof Role) {
            return $value->value;
        }

        return null;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Role
    {
        if ($value === null) {
            return null;
        }

        return Role::from($value);
    }

    public function getName(): string
    {
        return 'role';
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}