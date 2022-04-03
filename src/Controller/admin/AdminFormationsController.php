<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Controller\admin;

use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FormationRepository;
use App\Repository\NiveauRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Description of AdminFormationsController
 *
 * @author Romain
 */
class AdminFormationsController extends AbstractController {
   private const PAGEADMINFORMATIONS = "admin/admin.formations.html.twig";

    /**
     *
     * @var FormationRepository
     */
    private $repository;
    
    /**
     *
     * @var NiveauRepository
     */
    private $niveauRepository;
    
    /**
     * 
     * @var EntityManagerInterface
     */
    private $om;

    /**
     * 
     * @param FormationRepository $repository
     * @param NiveauRepository $niveauRepository
     */
    function __construct(FormationRepository $repository, NiveauRepository $niveauRepository) {
        $this->repository = $repository;
        $this->niveauRepository = $niveauRepository;
    }

    /**
     * @Route("/admin", name="admin.formations")
     * @return Response
     */
    public function index(): Response{
        $formations = $this->repository->findAll();
        $niveau = $this->niveauRepository->findAll();
        return $this->render(self::PAGEADMINFORMATIONS, [
            'formations' => $formations,
            'niveaux' => $niveau,
            'niveau_choose' => "débutant"
        ]);
    }
    
    /**
     * @Route("/admin/formations/tri/{champ}/{ordre}", name="admin.formations.sort")
     * @param type $champ
     * @param type $ordre
     * @return Response
     */
    public function sort($champ, $ordre): Response{
        $formations = $this->repository->findAllOrderBy($champ, $ordre);
        $niveau = $this->niveauRepository->findAll();
        return $this->render(self::PAGEADMINFORMATIONS, [
           'formations' => $formations,
           'niveaux' => $niveau,
           'niveau_choose' => "débutant"
        ]);
    }   
        
    /**
     * @Route("/admin/formations/recherche/{champ}/{isStrict}", name="admin.formations.findallcontain")
     * @param type $champ
     * @param boolean $isStrict
     * @param Request $request
     * @return Response
     */
    public function findAllContain($champ, $isStrict, Request $request): Response{
        $niveau = $this->niveauRepository->findAll();
        if($this->isCsrfTokenValid('filtre_'.$champ, $request->get('_token'))){
            $valeur = $request->get("recherche");
            $formations = $this->repository->findByContainValue($champ, $valeur, $isStrict);
            return $this->render(self::PAGEADMINFORMATIONS, [
                'formations' => $formations,
                'niveaux' => $niveau,
                'niveau_choose' => (int)$valeur
            ]);
        }
        return $this->redirectToRoute("formations");
    }  
    
    /**
     * @Route("/admin/edit/{id}", name="admin.formation.edit")
     * @param Formation $formation
     * @param Request $request
     * @return Response
     */
    public function edit(Formation $formation, Request $request): Response {
        $formFormation = $this->createForm(FormationType::class, $formation);
        $formFormation->handleRequest($request);
        if($formFormation->isSubmitted() && $formFormation->isValid()) {
            $this->om->flush();
            return $this->redirectToRoute('admin.formations');
        }
        
        return $this->render("admin/admin.formation.edit.html.twig", [
            'formation' => $formation,
            'formvisite' => $formFormation->createView()
        ]);
    }
    
    /**
     * @Route("/admin/suppr/{id}", name="admin.formation.suppr")
     * @param Formation $formation
     * @return Response
     */
    public function suppr(Formation $formation): Response {
        $this->om->remove($formation);
        $this->om->flush(); //permet d'envoyer des ordres vers la bdd
        return $this->redirectToRoute('admin.formations');
    }
}
