<?php

namespace App\Controller;

use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use SensioLabs\AnsiConverter\Theme\SolarizedTheme;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class SyncController extends AbstractController
{
    #[Route('/sync', name: 'sync')]
    public function index(Request $request, KernelInterface $kernel): Response
    {
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);

        $method = $request->query->get('method');

        $input = new ArrayInput([
            'command' => 'app:sync-scoresaber-users',
//          (optional) define the value of command arguments
//          'fooArgument' => 'barValue',
//          (optional) pass options to the command
            '--method' => $method,
        ]);

        // You can use NullOutput() if you don't need the output
        $output = new BufferedOutput(
            OutputInterface::VERBOSITY_NORMAL,
            true // true for decorated
        );

        $application->run($input, $output);

        $theme = new SolarizedTheme();
        $converter = new AnsiToHtmlConverter($theme, true);
        $content = $output->fetch();
        $styles = $theme->asCss();

        return $this->render('default/connector/synchronize.html.twig', [
            'html' => $converter->convert($content),
            'styles' => $styles,
        ]);
    }
}
