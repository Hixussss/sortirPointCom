<?php

// namespace App\DataFixtures;

// use App\Entity\City;
// use App\Entity\Site;
// use App\Entity\User;
// use App\Entity\Event;
// use App\Entity\State;
// use App\Entity\Location;
// use Doctrine\Bundle\FixturesBundle\Fixture;
// use Doctrine\Persistence\ObjectManager;
// use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// class AppFixtures extends Fixture
// {
//     private $passwordHasher;

//     public function __construct(UserPasswordHasherInterface $passwordHasher)
//     {
//         $this->passwordHasher = $passwordHasher;
//     }

//     public function load(ObjectManager $manager): void
//     {
//         $creationState = new State();
//         $creationState->setLabel('CREATION');
//         $manager->persist($creationState);

//         $openState = new State();
//         $openState->setLabel('REGISTRATION OPEN');
//         $manager->persist($openState);

//         $closedState = new State();
//         $closedState->setLabel('REGISTRATION CLOSED');
//         $manager->persist($closedState);

//         $onGoingState = new State();
//         $onGoingState->setLabel('ONGOING');
//         $manager->persist($onGoingState);

//         $canceledState = new State();
//         $canceledState->setLabel('CANCELED');
//         $manager->persist($canceledState);

//         $finishedState = new State();
//         $finishedState->setLabel('FINISHED');
//         $manager->persist($finishedState);

//         $archivedState = new State();
//         $archivedState->setLabel('ARCHIVED');
//         $manager->persist($archivedState);
        
//         $city = new City();
//         $city->setName('Nantes');
//         $city->setPostalCode('44300');
//         $manager->persist($city);

//         $location = new Location();
//         $location->setName('Patinoire du Petit Port');
//         $location->setCity($city);
//         $location->setStreet('Boulevard du Petit Port');
//         $location->setLongitude('-1.55560951810195');
//         $location->setLatitude('47.2429489943986');
//         $manager->persist($location);

//         $site = new Site();
//         $site->setName('Campus Nantes');
//         $manager->persist($site);

//         $user = new User();
//         $user->setEmail('test@example.com');
//         $user->setUsername('test');
//         $user->setLastname('TEST');
//         $user->setFirstname('Test');
//         $user->setSite($site);
//         $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
//         $manager->persist($user);

//         $event = new Event();
//         $event->setName("Sortie Patinoire");
//         $event->setStartDate(new \DateTime('2024-12-01'));
//         $event->setDuration(14400);
//         $event->setRegistrationEndDate(new \DateTime('2024-11-28'));
//         $event->setDescription('Sortie à la patinoire.');
//         $event->setMaxRegistrations(30);
//         $event->setLocation($location);
//         $event->setState($openState);
//         $event->setOrganizer($user);
//         $event->setOrganizerSite($site);

//         $event->addParticipant($user);


//         $city2 = new City();
//         $city2->setName('Saint-Herblain');
//         $city2->setPostalCode('44162');
//         $manager->persist($city2);

//         $location2 = new Location();
//         $location2->setName('Myoko');
//         $location2->setCity($city2);
//         $location2->setStreet('Cinéma UGC, Pl. Océane');
//         $location2->setLongitude('-1.6262348197574994');
//         $location2->setLatitude('47.22395054255178');
//         $manager->persist($location2);

//         $user2 = new User();
//         $user2->setEmail('lenaic@example.com');
//         $user2->setUsername('hixus');
//         $user2->setLastname('Barbier');
//         $user2->setFirstname('Lenaïc');
//         $user2->setSite($site);
//         $user2->setPassword($this->passwordHasher->hashPassword($user2, 'password123'));
//         $manager->persist($user2);

//         $user3 = new User();
//         $user3->setEmail('dorian@example.com');
//         $user3->setUsername('phyais');
//         $user3->setLastname('Pesce');
//         $user3->setFirstname('Dorian');
//         $user3->setSite($site);
//         $user3->setPassword($this->passwordHasher->hashPassword($user3, 'password123'));
//         $manager->persist($user3);

//         $user4 = new User();
//         $user4->setEmail('florian@example.com');
//         $user4->setUsername('flo2167');
//         $user4->setLastname('Heuzé');
//         $user4->setFirstname('Florian');
//         $user4->setSite($site);
//         $user4->setPassword($this->passwordHasher->hashPassword($user4, 'password123'));
//         $manager->persist($user4);

//         $event2 = new Event();
//         $event2->setName("Midi Myoko");
//         $event2->setStartDate(new \DateTime('2024-11-20'));
//         $event2->setDuration(14400);
//         $event2->setRegistrationEndDate(new \DateTime('2024-11-22'));
//         $event2->setDescription('La bonne bouffe à Myoko');
//         $event2->setMaxRegistrations(4);
//         $event2->setLocation($location2);
//         $event2->setState($openState);
//         $event2->setOrganizer($user);
//         $event2->setOrganizerSite($site);

//         $event2->addParticipant($user2);
//         $event2->addParticipant($user3);
//         $event2->addParticipant($user4);


//         $manager->persist($event);
//         $manager->persist($event2);

//         $manager->flush();
//     }


//     public static function getGroups(): array
//     {
//         return ['default'];
//     }
// }

// BIG FIXTURE
// <?php

// namespace App\DataFixtures;

// use App\Entity\User;
// use App\Entity\Event;
// use App\Entity\Location;
// use App\Entity\State;
// use App\Entity\City;
// use App\Entity\Site;
// use Doctrine\Bundle\FixturesBundle\Fixture;
// use Doctrine\Persistence\ObjectManager;
// use Faker\Factory;

// class AppFixtures extends Fixture
// {
//     public function load(ObjectManager $manager): void
//     {
//         $faker = Factory::create('en_US');

//         $openStates = [];
//         $openStateNames = ['Open', 'Closed', 'Cancelled', 'Pending'];
//         foreach ($openStateNames as $openStateName) {
//             $openState = new State();
//             $openState->setLabel($openStateName);
//             $manager->persist($openState);
//             $openStates[] = $openState;
//         }

//         $cities = [];
//         for ($i = 0; $i < 10; $i++) {
//             $city = new City();
//             $city->setName($faker->city);
//             $city->setPostalCode($faker->postcode);
//             $manager->persist($city);
//             $cities[] = $city;
//         }

//         $locations = [];
//         for ($i = 0; $i < 50; $i++) {
//             $location = new Location();
//             $location->setName($faker->company);
//             $location->setStreet($faker->streetAddress);
//             $location->setLatitude($faker->latitude);
//             $location->setLongitude($faker->longitude);
//             $location->setCity($cities[array_rand($cities)]);
//             $manager->persist($location);
//             $locations[] = $location;
//         }

//         $sites = [];
//         for ($i = 0; $i < 10; $i++) {
//             $site = new Site();
//             $site->setName($faker->company);
//             $manager->persist($site);
//             $sites[] = $site;
//         }

//         $users = [];
//         for ($i = 0; $i < 100; $i++) {
//             $user = new User();
//             $user->setUsername($faker->userName)
//                  ->setFirstName($faker->firstName)
//                  ->setLastName($faker->lastName)
//                  ->setEmail($faker->email)
//                  ->setPhone($faker->phoneNumber)
//                  ->setPassword(password_hash('password', PASSWORD_BCRYPT))
//                  ->setIsAdmin($faker->boolean(20))
//                  ->setIsActive(true)
//                  ->setSite($sites[array_rand($sites)]);
//             $manager->persist($user);
//             $users[] = $user;
//         }

//         for ($i = 0; $i < 200; $i++) {
//             $event = new Event();
//             $event->setName($faker->sentence(3))
//                   ->setStartDate($faker->dateTimeBetween('now', '+6 months'))
//                   ->setDuration($faker->numberBetween(1, 8) * 60)
//                   ->setRegistrationEndDate($faker->dateTimeBetween('-1 month', 'now'))
//                   ->setMaxRegistrations($faker->numberBetween(10, 100))
//                   ->setDescription($faker->paragraph)
//                   ->setLocation($locations[array_rand($locations)])
//                   ->setState($openStates[array_rand($openStates)])
//                   ->setOrganizer($users[array_rand($users)])
//                   ->setOrganizerSite($sites[array_rand($sites)]);

//             for ($j = 0; $j < $faker->numberBetween(5, $event->getMaxRegistrations()); $j++) {
//                 $event->addParticipant($users[array_rand($users)]);
//             }

//             $manager->persist($event);
//         }

//         $manager->flush();
//     }
// }


namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\City;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des états
        $states = ['CREATION', 'REGISTRATION OPEN', 'REGISTRATION CLOSED', 'ONGOING', 'CANCELED', 'FINISHED', 'ARCHIVED'];
        $stateEntities = [];
        foreach ($states as $stateLabel) {
            $state = new State();
            $state->setLabel($stateLabel);
            $manager->persist($state);
            $stateEntities[$stateLabel] = $state;
        }

        // Création des villes
        $cities = [];
        $cityData = [
            ['name' => 'Paris', 'postalCode' => '75001'],
            ['name' => 'Lyon', 'postalCode' => '69001'],
            ['name' => 'Bordeaux', 'postalCode' => '33000'],
            ['name' => 'Marseille', 'postalCode' => '13001'],
            ['name' => 'Toulouse', 'postalCode' => '31000'],
        ];
        foreach ($cityData as $data) {
            $city = new City();
            $city->setName($data['name']);
            $city->setPostalCode($data['postalCode']);
            $manager->persist($city);
            $cities[] = $city;
        }

        // Création des emplacements
        $locations = [];
        $locationData = [
            ['name' => 'Palais des Congrès', 'street' => '2 Place de la Porte Maillot', 'city' => $cities[0]],
            ['name' => 'Hôtel de Ville de Lyon', 'street' => '1 Place de la Comédie', 'city' => $cities[1]],
            ['name' => 'Cité du Vin', 'street' => '134 Quai de Bacalan', 'city' => $cities[2]],
            ['name' => 'Vieux-Port', 'street' => '1 Quai des Belges', 'city' => $cities[3]],
            ['name' => 'Capitole', 'street' => 'Place du Capitole', 'city' => $cities[4]],
        ];
        foreach ($locationData as $data) {
            $location = new Location();
            $location->setName($data['name']);
            $location->setStreet($data['street']);
            $location->setLatitude(rand(43000000, 49000000) / 1000000);
            $location->setLongitude(rand(-5000000, 5000000) / 1000000);
            $location->setCity($data['city']);
            $manager->persist($location);
            $locations[] = $location;
        }

        // Création des sites
        $sites = [];
        $siteData = ['Campus Paris', 'Campus Lyon', 'Campus Bordeaux', 'Campus Marseille', 'Campus Toulouse'];
        foreach ($siteData as $siteName) {
            $site = new Site();
            $site->setName($siteName);
            $manager->persist($site);
            $sites[] = $site;
        }

        // Création des utilisateurs
        $users = [];
        $userData = [
            ['username' => 'john.doe', 'firstName' => 'John', 'lastName' => 'Doe', 'email' => 'john.doe@example.com', 'site' => $sites[0]],
            ['username' => 'jane.smith', 'firstName' => 'Jane', 'lastName' => 'Smith', 'email' => 'jane.smith@example.com', 'site' => $sites[1]],
            ['username' => 'emma.jones', 'firstName' => 'Emma', 'lastName' => 'Jones', 'email' => 'emma.jones@example.com', 'site' => $sites[2]],
            ['username' => 'lucas.martin', 'firstName' => 'Lucas', 'lastName' => 'Martin', 'email' => 'lucas.martin@example.com', 'site' => $sites[3]],
            ['username' => 'olivia.brown', 'firstName' => 'Olivia', 'lastName' => 'Brown', 'email' => 'olivia.brown@example.com', 'site' => $sites[4]],
        ];
        foreach ($userData as $data) {
            $user = new User();
            $user->setUsername($data['username']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setEmail($data['email']);
            $user->setSite($data['site']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Création des événements
        $eventData = [
            ['name' => 'Symfony Conférence', 'location' => $locations[0], 'organizer' => $users[0], 'state' => $stateEntities['REGISTRATION OPEN']],
            ['name' => 'Laravel Workshop', 'location' => $locations[1], 'organizer' => $users[1], 'state' => $stateEntities['ONGOING']],
            ['name' => 'PHP Meetup', 'location' => $locations[2], 'organizer' => $users[2], 'state' => $stateEntities['REGISTRATION CLOSED']],
            ['name' => 'React Summit', 'location' => $locations[3], 'organizer' => $users[3], 'state' => $stateEntities['CANCELED']],
            ['name' => 'DevOps Bootcamp', 'location' => $locations[4], 'organizer' => $users[4], 'state' => $stateEntities['FINISHED']],
        ];
        foreach ($eventData as $data) {
            $event = new Event();
            $event->setName($data['name']);
            $event->setStartDate(new \DateTime('2024-12-' . rand(10, 20) . ' ' . rand(9, 18) . ':00:00'));
            $event->setDuration(rand(60, 240));
            $event->setRegistrationEndDate(new \DateTime('2024-12-' . rand(5, 10) . ' 23:59:59'));
            $event->setMaxRegistrations(rand(10, 100));
            $event->setDescription("Description de l'événement {$data['name']}");
            $event->setLocation($data['location']);
            $event->setState($data['state']);
            $event->setOrganizer($data['organizer']);
            $event->setOrganizerSite($data['organizer']->getSite());
            foreach ($users as $participant) {
                if ($participant !== $data['organizer'] && rand(0, 1)) {
                    $event->addParticipant($participant);
                }
            }
            $manager->persist($event);
        }

        $manager->flush();
    }
}
