<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class MessageFixtures extends Fixture implements DependentFixtureInterface
{
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i++) {
            $message = new Message();
            $message->setTitre($this->faker->sentence(3));
            $message->setDatePoste($this->faker->dateTimeThisYear());
            $message->setContenu($this->faker->paragraph());

            // Corriger getReference avec le deuxième argument
            $user = $this->getReference('user' . mt_rand(0, 9), User::class);
            $message->setUser($user);

            $manager->persist($message);
            $this->addReference('message' . $i, $message);
        }

        for ($i = 0; $i < 20; $i++) {
            $message = new Message();
            $message->setTitre($this->faker->sentence(3));
            $message->setDatePoste($this->faker->dateTimeThisYear());
            $message->setContenu($this->faker->paragraph());

            $user = $this->getReference('user' . mt_rand(0, 9), User::class);
            $message->setUser($user);

            // Correction ici aussi pour getReference avec Message::class
            $parentMessage = $this->getReference('message' . mt_rand(0, 9), Message::class);
            $message->setParent($parentMessage);

            $manager->persist($message);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
