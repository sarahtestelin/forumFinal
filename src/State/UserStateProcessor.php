<?php

namespace App\State;

// https://api-platform.com/docs/core/state-processors/
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserStateProcessor implements ProcessorInterface
{
    private $passwordHasher;
    private $decorated;
    public function __construct(private ProcessorInterface $persistProcessor,
        UserPasswordHasherInterface $passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }
    public function process($data, Operation $operation, array $uriVariables = [], array $context =
        []) {
// ca appelle la couche de persistance pour sauvegarder les données
        if ($data->getPassword()) {
            $data->setPassword(
                $this->passwordHasher->hashPassword($data, $data->getPassword())
            );
        }
        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        return $result;
    }
}
