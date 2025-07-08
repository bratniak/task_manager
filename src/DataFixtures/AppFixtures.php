<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statuses = ['todo', 'doing', 'done'];

        for ($i=0; $i < 10; $i++) { 
            $task = new Task();
            $task->setTitle((string)('Task: ' . $i));
            $task->setDescription((string)('Description task: ' . $i));;
            $task->setStatus($statuses[array_rand($statuses)]);
            $manager->persist($task);
        }
        
        $manager->flush();
    }
}
