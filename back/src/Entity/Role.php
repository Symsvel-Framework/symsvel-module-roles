<?php

declare(strict_types=1);

namespace SymSvel\Module\Roles\Entity;

use Doctrine\ORM\Mapping as ORM;
use SymSvel\Module\Roles\Repository\RoleRepository;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'symsvel_roles')]
#[ORM\UniqueConstraint(name: 'uniq_symsvel_roles_code', columns: ['code'])]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $code;

    #[ORM\Column(length: 150)]
    private string $label;

    #[ORM\Column(name: 'is_system', options: ['default' => false])]
    private bool $system = false;

    public function __construct(string $code, string $label, bool $system = false)
    {
        if (strlen($code) > 100 || !preg_match('/^ROLE_[A-Z][A-Z0-9_]*$/D', $code)) {
            throw new \InvalidArgumentException('Invalid role code.');
        }

        if (trim($label) === '' || strlen($label) > 150) {
            throw new \InvalidArgumentException('Invalid role label.');
        }
        $this->code = $code;
        $this->label = $label;
        $this->system = $system;
    }

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getLabel(): string { return $this->label; }
    public function isSystem(): bool { return $this->system; }
    public function rename(string $label): void {
        if (trim($label) === '' || strlen($label) > 150) {
            throw new \InvalidArgumentException('Invalid role label.');
        }
        $this->label = trim($label);
    }
}
