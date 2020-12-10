<?php

namespace App\Security\Voter;

use App\Entity\Grade;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessGradeVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['read', 'edit', 'delete'])
            && $subject instanceof Grade;
    }

    protected function voteOnAttribute($attribute, $grade, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $families = $user->getFamilies($user);

        foreach ($families as $familyRow) {
            
            $children = $familyRow->getChildren()->getValues();
            
            foreach ($children as $child) {
                $grades = $child->getGrades()->getValues();
            
                if (in_array($grade, $grades)){
                    
            // ... (check conditions and return true to grant permission) ...
                    switch ($attribute) {
                    case 'read':
                        // logic to determine if the user can EDIT
                        return true;
                        break;
                    case 'edit':
                        // logic to determine if the user can VIEW
                        return true;
                        break;
                    case 'delete':
                        // logic to determine if the user can EDIT
                        return true;
                        break;
                }
            }
            }
        }
        return false;
    }
}
