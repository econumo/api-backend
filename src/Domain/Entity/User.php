<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Entity\ValueObject\CurrencyCode;
use App\Domain\Entity\ValueObject\Email;
use App\Domain\Entity\ValueObject\Id;
use App\Domain\Entity\ValueObject\Identifier;
use App\Domain\Entity\ValueObject\ReportPeriod;
use App\Domain\Events\UserRegisteredEvent;
use App\Domain\Traits\EventTrait;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EventTrait;

    /**
     * @var string E-mail
     */
    private string $identifier;

    /**
     * @var string The hashed password
     */
    private string $password;

    /**
     * @var Collection|self[]
     */
    private Collection $connections;

    /**
     * @var Collection|UserOption[]
     */
    private Collection $options;

    private DateTimeImmutable $createdAt;

    private DateTimeInterface $updatedAt;

    public function __construct(private Id $id, /**
     * @var string The salt
     */
    private string $salt, private string $name, Email $email, DateTimeInterface $createdAt)
    {
        $this->identifier = Identifier::createFromEmail($email)->getValue();
        $this->connections = new ArrayCollection();
        $this->options = new ArrayCollection();
        $this->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->updatedAt = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt->format('Y-m-d H:i:s'));
        $this->registerEvent(new UserRegisteredEvent($id));
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function updatePassword(string $password): void
    {
        $this->password = $password;
    }

    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getAvatarUrl(): string
    {
        return sprintf('https://www.gravatar.com/avatar/%s', md5($this->identifier));
    }

    public function isUserConnected(self $user): bool
    {
        return $this->connections->contains($user);
    }

    public function connectUser(self $user): void
    {
        if ($user->getId()->isEqual($this->getId())) {
            return;
        }

        $this->connections->add($user);
    }

    public function deleteConnection(self $user): void
    {
        $this->connections->removeElement($user);
    }

    /**
     * @return self[]|Collection
     */
    public function getConnections(): Collection
    {
        return $this->connections;
    }

    public function createOption(UserOption $option): UserOption
    {
        foreach ($this->options as $item) {
            if ($item->getName() === $option->getName()) {
                $this->options->removeElement($item);
            }
        }

        $this->options->add($option);
        return $option;
    }

    public function getOption(string $name): ?UserOption
    {
        foreach ($this->options as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        return null;
    }

    public function deleteOption(string $name): void
    {
        foreach ($this->options as $item) {
            if ($item->getName() === $name) {
                $this->options->removeElement($item);
            }
        }
    }

    /**
     * @return UserOption[]|Collection
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function getCurrency(): CurrencyCode
    {
        foreach ($this->options as $option) {
            if ($option->getName() === UserOption::CURRENCY) {
                return new CurrencyCode($option->getValue());
            }
        }

        return new CurrencyCode(UserOption::DEFAULT_CURRENCY);
    }

    public function getReportPeriod(): ReportPeriod
    {
        foreach ($this->options as $option) {
            if ($option->getName() === UserOption::REPORT_PERIOD) {
                return new ReportPeriod($option->getValue());
            }
        }

        return new ReportPeriod(UserOption::DEFAULT_REPORT_PERIOD);
    }

    public function updateCurrency(CurrencyCode $currencyCode): void
    {
        foreach ($this->options as $option) {
            if ($option->getName() === UserOption::CURRENCY) {
                $option->updateValue($currencyCode->getValue());
            }
        }
    }

    public function updateReportPeriod(ReportPeriod $reportPeriod): void
    {
        foreach ($this->options as $option) {
            if ($option->getName() === UserOption::CURRENCY) {
                $option->updateValue($reportPeriod->getValue());
            }
        }
    }
}
