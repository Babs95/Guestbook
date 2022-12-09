<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class ConferenceController extends AbstractController
{
    protected $conferenceRepository;
    protected $commentRepository;

    public function __construct(ConferenceRepository $conferenceRepository, CommentRepository $commentRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function index(LoggerInterface $logger): Response
    {
        // retrieve the object from database
        $conferences = $this->conferenceRepository->findAll();
        if (!$conferences) {
            throw $this->createNotFoundException('The conferences does not exist');
            // the above is just a shortcut for:
            // throw new NotFoundHttpException('The product does not exist');
        }
        /*$greet = '';
        if ($name = $request->query->get('hello')) {
            $greet = sprintf('<h1>Hello %s!</h1>', htmlspecialchars($name));
        }

        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'greet' => $greet,
        ])*/
        //return new Response('<html><body> $greet <img src="/images/under-construction.gif" /></body></html>');

        /*return new Response($twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll(),
        ]));*/

        $logger->info('Conference Controller index called!');

        // the template path is the relative file path from `templates/`
        return $this->render('conference/index.html.twig', [
            // this array defines the variables passed to the template,
            // where the key is the variable name and the value is the variable value
            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
            'conferences' => $conferences,
        ]);

    }

    /**
     * @Route("/conference/{id}", name="conference")
     */
    public function show(Request $request, Conference $conference): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }
}
