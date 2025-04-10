<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('link', 'Lien de l\'image')
                ->setHelp('Entrez l\'URL directe de l\'image')
                ->hideOnIndex(),
            ImageField::new('link', 'Prévisualisation')
                ->setBasePath('') // Si l'image est une URL complète, laissez vide
                ->onlyOnIndex()

        ];
    }
}
