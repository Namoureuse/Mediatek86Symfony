<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FormationRepository;
use App\Repository\NiveauRepository;

/**
 * Description of FormationsController
 *
 * @author emds
 */
class FormationsController extends AbstractController {
    
    private const PAGEFORMATIONS = "pages/formations.html.twig";

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
     * @param FormationRepository $repository
     * @param NiveauRepository $niveauRepository
     */
    function __construct(FormationRepository $repository, NiveauRepository $niveauRepository) {
        $this->repository = $repository;
        $this->niveauRepository = $niveauRepository;
    }

    /**
     * @Route("/formations", name="formations")
     * @return Response
     */
    public function index(): Response{
        $formations = $this->repository->findAll();
        $niveau = $this->niveauRepository->findAll();
        return $this->render(self::PAGEFORMATIONS, [
            'formations' => $formations,
            'niveaux' => $niveau
        ]);
    }
    
    /**
     * @Route("/formations/tri/{champ}/{ordre}", name="formations.sort")
     * @param type $champ
     * @param type $ordre
     * @return Response
     */
    public function sort($champ, $ordre): Response{
        $formations = $this->repository->findAllOrderBy($champ, $ordre);
        return $this->render(self::PAGEFORMATIONS, [
           'formations' => $formations
        ]);
    }   
        
    /**
     * @Route("/formations/recherche/{champ}/{isStrict}", name="formations.findallcontain")
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
            return $this->render(self::PAGEFORMATIONS, [
                'formations' => $formations,
                'niveaux' => $niveau,
                'niveau_choose' => (int) $valeur
            ]);
        }
        return $this->redirectToRoute("formations");
    }  
    
    /**
     * @Route("/formations/formation/{id}", name="formations.showone")
     * @param type $id
     * @return Response
     */
    public function showOne($id): Response{
        $formation = $this->repository->find($id);
        return $this->render("pages/formation.html.twig", [
            'formation' => $formation,
            'niveaux' => $niveau
        ]);        
    }    
}
