<?php

namespace Lw\Domain\Model\User;

use Assert\Assertion;
use Ddd\Domain\DomainEventPublisher;
use Lw\Domain\Model\Wish\Wish;
use Lw\Domain\Model\Wish\WishId;
use Lw\Domain\Model\Wish\WishWasMade;

/**
 * Class User.
 */
class User
{
    const MAX_LENGTH_EMAIL = 255;
    const MAX_LENGTH_PASSWORD = 255;

    /**
     * @var UserId
     */
    protected $userId;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var \DateTime
     */
    protected $createdOn;

    /**
     * @var \DateTime
     */
    protected $updatedOn;

    /**
     * @param UserId $userId
     * @param string $email
     * @param string $password
     */
    public function __construct(UserId $userId, $email, $password)
    {
        $this->userId = $userId;
        $this->setEmail($email);
        $this->changePassword($password);
        $this->createdOn = new \DateTime();
        $this->updatedOn = new \DateTime();

        DomainEventPublisher::instance()->publish(
            new UserRegistered(
                $this->userId
            )
        );
    }

    /**
     * @return UserId
     */
    public function id()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function changePassword($password)
    {
        $password = trim($password);
        if (!$password) {
            throw new \InvalidArgumentException('password');
        }

        $this->password = $password;
    }

    public function makeWishNotBeingAnAggregate(WishId $wishId, $address, $content)
    {
        $newWish = new Wish(
            $wishId,
            $this->id(),
            $address,
            $content
        );

        DomainEventPublisher::instance()->publish(
            new WishWasMade(
                $newWish->id(),
                $newWish->userId(),
                $newWish->address(),
                $newWish->content()
            )
        );

        return $newWish;
    }

    public function makeWishBeingAnAggregate(WishId $wishId, $address, $content)
    {
        $this->wishes[] = new Wish(
            $wishId,
            $this->id(),
            $address,
            $content
        );
    }

    public function grantWishes()
    {
        $wishesGranted = 0;
        foreach ($this->wishes as $wish) {
            $wish->grant();
            ++$wishesGranted;
        }

        return $wishesGranted;
    }

    /**
     * @param $email
     */
    protected function setEmail($email)
    {
        $email = trim($email);
        if (!$email) {
            throw new \InvalidArgumentException('email');
        }

        Assertion::email($email);
        $this->email = strtolower($email);
    }
}
