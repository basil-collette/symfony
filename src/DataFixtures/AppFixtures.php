<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    //cmd to load the fixtures
    //php bin/console doctrine:fixtures/load

    /**
    * @var Generator
    */
    private Generator $faker;
        
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $pseudoAdmin = "admin";
        $hashedPasswordAdmin = $this->passwordHasher->hashPassword(
                    $admin,
                    $pseudoAdmin
                );
        $admin->setPassword($hashedPasswordAdmin)
            ->setUsername($pseudoAdmin)
            ->setRoles(array("ROLE_ADMIN"))
            ;

        $manager->persist($admin);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $pseudo = $this->faker->name();
            $hashedPassword = $this->passwordHasher->hashPassword(
                        $user,
                        $pseudo
                    );
            $user->setPassword($hashedPassword)
                ->setUsername($pseudo);

            $manager->persist($user);

            $randomArticlesNumber = rand(1, 10);

            for ($j = 0; $j < $randomArticlesNumber; $j++) {
                $article = new Article();
                $article->setContenu($this->faker->paragraph())
                    ->setTitre($this->faker->sentence())
                    ->setAuteur($user)
                    ->setDatetime($this->faker->dateTime())
                    ->setLikes(array())
                    ->setDislikes(array())
                    ;

                $manager->persist($article);

                $randomCommentsNumber = rand(1, 5);

                for ($k = 0; $k < $randomCommentsNumber; $k++) {
                    $comment = new Comment();
                    $comment->setContenu($this->faker->sentence())
                        ->setDate($this->faker->dateTime())
                        ->setAuteur($user)
                        ->setArticle($article)
                        ->setLikes(array())
                        ->setDislikes(array())
                        ;

                    $manager->persist($comment);
                }
            }
        }
        
        /*
        //USERS
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $hashedPassword = $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    );
            $user->setPassword($hashedPassword)
                ->setPseudo($this->faker->name());

            $manager->persist($user);
        }
        */
        
        /*
        //ARTICLES
        for ($i = 0; $i < 50; $i++) {
            $article = new Article();
            $article->setContenu($this->faker->paragraph())
                ->setTitre($this->faker->sentence())
                ->setAuteur()
                ->setDatetime();

            $manager->persist($article);
        }
        */
        
        /*
        //COMMENTS
        for ($i = 0; $i < 50; $i++) {
            $comment = new Comment();
            $comment->setContenu($this->faker->sentence())
                ->setDate($this->faker->dateTime())
                ->setAuteur()
                ->setArticle($article)

            $manager->persist($article);
        }
        */

        $manager->flush();
    }
}
