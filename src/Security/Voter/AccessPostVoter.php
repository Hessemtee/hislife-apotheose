<?php

namespace App\Security\Voter;

use App\Entity\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessPostVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'read', 'delete'])
            && $subject instanceof Post;
    }

    protected function voteOnAttribute($attribute, $post, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $families= $user->getFamilies($user);
        
        foreach ($families as $familyRow) 
        {
            $posts = $familyRow->getPosts()->getValues();

                if (in_array($post, $posts)) {
            // ... (check conditions and return true to grant permission)
                    switch ($attribute) {
                case 'edit':
                    return true;
                    break;
                case 'read':
                        //     // logic to determine if the user can VIEW
                    return true;
                    break;

                case 'delete':
                    return true;
                    break;
                }
                } 
        }

        return false;
    }
}
