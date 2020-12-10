<?php

namespace App\Security\Voter;

use App\Entity\Evenement;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessEvenementVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['show', 'delete', 'edit'])
            && $subject instanceof Evenement;
    }

    protected function voteOnAttribute($attribute, $evenement, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        $families = $user->getFamilies($user);

        foreach ($families as $familyRow) {

            $evenements = $familyRow->getEvenements()->getValues();

            foreach ($evenements as $evenementRow) {

                if($evenementRow === $evenement){
        // ... (check conditions and return true to grant permission) ...
                switch ($attribute) {
            case 'show':
                // logic to determine if the user can show
                return true;
                break;
            case 'delete':
                // logic to determine if the user can delete
                return true;
                break;
            case 'edit':
            // logic to determine if the user can delete
                return true;
                break;
            }
            }
            }
        }
        return false;
    }
}
