<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Entity\User;
use App\Entity\Owner;
use App\Entity\Client;

class UserFixtures extends Fixture
{
    private $passwordEncoder;
    
         public function __construct(UserPasswordEncoderInterface $passwordEncoder)
         {
             $this->passwordEncoder = $passwordEncoder;
        }
        
    public function load(ObjectManager $manager)
    {
        $this->LoadUsers($manager);

        $manager->flush();
    }
    private function loadUsers(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$email,$plainPassword,$role]) {
            $user = new User();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setEmail($email);
            $user->setPassword($encodedPassword);
            $user->addRole($role);
            $manager->persist($user);
        }

        foreach ($this->getOwnerData() as [$email,$plainPassword,$role, $familyname, $firstname, $address, $country]) {
            $owner = new Owner();
            $owner->setFamilyname($familyname);
            $owner->setFfirstname($firstname);
            $owner->setAddress($address);
            $owner->setCountry($country);
            $manager->persist($user);
            $user = new User();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setEmail($email);
            $user->setPassword($encodedPassword);
            $user->addRole($role);
            $user->setOwner($owner);
            $manager->persist($user);
        }

        foreach ($this->getClientData() as [$email,$plainPassword,$role, $familyname, $firstname, $address, $country]) {
            $client = new Client();
            $client->setFamilyname($familyname);
            $client->setFirstname($firstname);
            $client->setAddress($address);
            $client->setCountry($country);
            $manager->persist($user);
            $user = new User();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
            $user->setEmail($email);
            $user->setPassword($encodedPassword);
            $user->addRole($role);
            $user->setClient($client);
            $manager->persist($user);
        }
    }
    
    private function getUserData()
    {
        yield ['admin@ccint.eu','admin','ROLE_ADMIN'];        
    }

    private function getOwnerData()
    {        
        yield ['owner@ccint.eu','owner','ROLE_USER', 'OwnerFamilyame', 'OwnerFirstname', 'Owner Address', 'FR'];   
    }

    private function getClientData()
    {        
        yield ['client@ccint.eu','client','ROLE_USER', 'ClientFamilyame', 'ClientFirstname', 'Client Address', 'FR'];   
    }
}
