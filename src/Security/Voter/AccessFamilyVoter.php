<?php

namespace App\Security\Voter;

use App\Entity\Family;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessFamilyVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['edit', 'view', 'delete', 'create'])
            && $subject instanceof Family;
    }

    protected function voteOnAttribute($attribute, $family, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
    
        $families= $user->getFamilies($user);
        
        foreach ($families as $familyRow) {
            if ($familyRow === $family) {

            // ... (check conditions and return true to grant permission) ...
                switch ($attribute) {
                case 'edit':
                    return true;
                    break;
                case 'view':
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
