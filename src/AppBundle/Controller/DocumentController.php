<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

final class DocumentController extends Controller
{
    public function collectionAction()
    {
        return $this->render('Document/collection.html.twig');
    }
}
