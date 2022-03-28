<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('full_name', 'Полное имя'),
            EmailField::new('email'),
        ];

        // yield AssociationField::new('id');
        // yield TextField::new('full_name');
    }
    

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Пользователи')
            ->setSearchFields(['full_name'])
            ->setDefaultSort(['id' => 'ASC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
{
    $viewUser = Action::new('showUserProfile', 'Открыть профиль')
        ->linkToRoute('app_profile', function (User $user): array {
            return [
                'id' => $user->getId()
            ];
        });

    return $actions
        ->add(Crud::PAGE_INDEX, $viewUser)
        ->remove(Crud::PAGE_INDEX, Action::EDIT)
        ->remove(Crud::PAGE_INDEX, Action::NEW)
    ;
}

    

}
