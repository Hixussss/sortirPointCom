<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CsvUserImporter
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    public function previewWithValidation(string $filePath): array
    {
        $previewData = [];
        $issues = [];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('The file cannot be read.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the file.');
        }

        $header = fgetcsv($handle);
        $existingEmails = $this->getExistingEmails();
        $existingUsernames = $this->getExistingUsernames();

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);
            $row['issues'] = [];

            if (in_array($row['email'], $existingEmails)) {
                $row['issues'][] = 'Duplicate email';
            }

            if (in_array($row['username'], $existingUsernames)) {
                $row['issues'][] = 'Duplicate username';
            }

            $previewData[] = $row;
        }

        fclose($handle);

        return $previewData;
    }

    private function getExistingEmails(): array
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return array_map(fn(User $user) => $user->getEmail(), $users);
    }

    private function getExistingUsernames(): array
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return array_map(fn(User $user) => $user->getUsername(), $users);
    }

    public function preview(string $filePath): array
    {
        $previewData = [];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('The file cannot be read.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the file.');
        }

        $header = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            $previewData[] = array_combine($header, $data);
        }

        fclose($handle);

        return $previewData;
    }

    public function finalizeImport(array $data): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
        ];
    
        foreach ($data as $row) {
            try {
                // Vérifier et ajouter un verificationToken si absent
                if (!isset($row['verificationToken']) || empty($row['verificationToken'])) {
                    $row['verificationToken'] = bin2hex(random_bytes(32));
                }
    
                $user = $this->createOrUpdateUser($row);
                $this->entityManager->persist($user);
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }
    
        $this->entityManager->flush();
    
        return $results;
    }
    
    

    public function import(string $filePath): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
        ];

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('The file cannot be read.');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open the file.');
        }
        
        $header = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $user = $this->createOrUpdateUser(array_combine($header, $data));
                $this->entityManager->persist($user);
                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }
        }

        fclose($handle);

        $this->entityManager->flush();

        return $results;
    }

    private function createOrUpdateUser(array $data): User
    {
        $site = $this->entityManager->getRepository(Site::class)->findOneBy(['name' => $data['site']]);
        if (!$site) {
            $site = new Site();
            $site->setName($data['site']);
            $this->entityManager->persist($site);
        }
    
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findOneBy(['email' => $data['email']]) ?? new User();
    
        $user
            ->setUsername($data['username'])
            ->setEmail($data['email'])
            ->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setPhone($data['phone'])
            ->setSite($site)
            ->setIsAdmin((bool)$data['isAdmin'])
            ->setIsActive(true);
    
        if (isset($data['verificationToken']) && !empty($data['verificationToken'])) {
            $user->setVerificationToken($data['verificationToken']);
        } else {
            $user->setVerificationToken(bin2hex(random_bytes(32)));
        }
    
        if ($user->getPassword() === null || $user->getPassword() !== $data['password']) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
    
        return $user;
    }
    
    


}
