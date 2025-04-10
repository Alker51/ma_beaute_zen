<?php

namespace App\DataFixtures;

use App\Entity\Gender;
use App\Entity\Tax;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $genders = ['Homme', 'Femme', 'Autre / Ne ce prononce pas'];
        $genderEntities = [];

        foreach ($genders as $gender) {
            $genre = new Gender()
                ->setName(ucfirst($gender));
            $manager->persist($genre);

            $genderEntities[$gender] = $genre;
        }

        $manager->flush();

        $admin = new User()
            ->setEmail('admin@example.com') // Email de l'administrateur
            ->setFirstName('ADMIN')
            ->setLastName('ISTRATOR')
            ->setPhone('0123456789')
            ->setAdress('123 Rue des Admins')
            ->setCity('AdminCity')
            ->setZipcode('12345')
            ->setBirthDate(new \DateTime('1980-01-01')) // Date de naissance
            ->setGender($genderEntities['Homme']) // Genre associé
            ->setWantNewsletter(true)
            // Définir le rôle d'admin
            ->setRoles(['ROLE_ADMIN']);

        // Hachage et définition du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $manager->flush();

        $tvaRates = [
            ['name' => 'TVA normale 20%', 'value' => 20.0],   // Taux normal
            ['name' => 'TVA intermédiaire 10%', 'value' => 10.0], // Taux intermédiaire
            ['name' => 'TVA réduite 5,5%', 'value' => 5.5],       // Taux réduit
            ['name' => 'TVA super réduite 2,1%', 'value' => 2.1], // Taux super réduit
        ];

        foreach ($tvaRates as $tvaRate) {
            $tax = new Tax()
                ->setName($tvaRate['name'])
                ->setValue($tvaRate['value']);

            $manager->persist($tax);
        }

        $manager->flush();

    }
}
