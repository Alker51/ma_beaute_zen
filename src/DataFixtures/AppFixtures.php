<?php

namespace App\DataFixtures;

use App\Entity\Gender;
use App\Entity\Image;
use App\Entity\Produit;
use App\Entity\State;
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
        $genderEntities = $this->generateGender($manager);
        $manager->flush();

        $admin = $this->generateAdmin($genderEntities);
        $manager->persist($admin);
        $manager->flush();

        $user = $this->generateUser($genderEntities);
        $manager->persist($user);
        $manager->flush();

        $taxEntities = $this->generateTaxes($manager);
        $manager->flush();

        $produit = $this->generateProduits($taxEntities);
        $manager->persist($produit);
        $manager->flush();

        $image = $this->generateImage($produit);
        $manager->persist($image);
        $manager->flush();

        $this->generateState($manager);
        $manager->flush();

    }

    public function generateProduits(array $tax): Produit
    {
        return new Produit()
            ->setName('Produit test')
            ->setDescription('Description du produit test')
            ->setDelay(35)
            ->setActive(true)
            ->setPriceHT(9.99)
            ->setPromoActive(false)
            ->setTaxeId($tax['TVA normale 20%'])
            ->setStock(15)
            ->setNoStockProduct(false);
    }

    public function generateTaxes(ObjectManager $manager): array
    {
        $tvaRates = [
            ['name' => 'TVA normale 20%', 'value' => 20.0],   // Taux normal
            ['name' => 'TVA intermédiaire 10%', 'value' => 10.0], // Taux intermédiaire
            ['name' => 'TVA réduite 5,5%', 'value' => 5.5],       // Taux réduit
            ['name' => 'TVA super réduite 2,1%', 'value' => 2.1], // Taux super réduit
        ];

        $taxEntities = [];

        foreach ($tvaRates as $tvaRate) {
            $tax = new Tax()
                ->setName($tvaRate['name'])
                ->setValue($tvaRate['value']);

            $manager->persist($tax);

            $taxEntities[$tvaRate['name']] = $tax;
        }

        return $taxEntities;
    }

    public function generateGender(ObjectManager $manager) :array
    {
        $genders = ['Homme', 'Femme', 'Autre / Ne ce prononce pas'];
        $genderEntities = [];

        foreach ($genders as $gender) {
            $genre = new Gender()
                ->setName(ucfirst($gender));
            $manager->persist($genre);

            $genderEntities[$gender] = $genre;
        }

        return $genderEntities;
    }

    public function generateAdmin(array $genderEntities) :User
    {
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
            ->setRoles(['ROLE_ADMIN'])
            ->setVisitor(false);

        // Hachage et définition du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        return $admin;
    }

    private function generateImage(Produit $produit): Image
    {
        return new Image()
            ->setLink('https://i.ibb.co/VWCsjN02/a6bf59e8-7880-48b2-83e9-d7bd10af430c.jpg')
            ->addProduit($produit);
    }

    private function generateState(ObjectManager $manager) :void
    {
        $states = ["En cours de traitement", "Résolue", "Abandonnée", "Validé", "En attente", "Refusé", "Réalisé", "Annulé"];

        foreach ($states as $state){
            $statement = new State();
            $statement->setName($state);
            $manager->persist($statement);
        }
    }

    private function generateUser(array $genderEntities)
    {
        $user = new User()
            ->setEmail('test@example.com') // Email de l'administrateur
            ->setFirstName('Rémy')
            ->setLastName('Robin')
            ->setPhone('0123456789')
            ->setAdress('123 Rue des test')
            ->setCity('TestCity')
            ->setZipcode('12345')
            ->setBirthDate(new \DateTime('1998-02-06')) // Date de naissance
            ->setGender($genderEntities['Homme']) // Genre associé
            ->setWantNewsletter(true)
            // Définir le rôle d'admin
            ->setRoles(['ROLE_USER'])
            ->setVisitor(false);

        // Hachage et définition du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);

        return $user;
    }
}