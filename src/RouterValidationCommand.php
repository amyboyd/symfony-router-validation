<?php

namespace AmyBoyd\SymfonyStuff\RouterValidation;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Route;

final class RouterValidationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('router:validate')
            ->setDescription('Validate that routes are pointing to valid controllers/actions');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->getContainer()->get('router')->getRouteCollection();
        $validationService = $this->getContainer()->get('amyboyd.router_validation');
        $result = $validationService->validateRoutes($routes);

        $validCount = $result['validCount'];
        $invalidRoutes = $result['invalidRoutes'];
        $unsupportedRoutes = $result['unsupportedRoutes'];

        $output->writeln('<info>' . $validCount . ' valid routes</info>');

        if (count($invalidRoutes) === 0) {
            $output->writeln('<info>No invalid routes</info>');
        } else {
            $output->writeln('<error>' . count($invalidRoutes) . ' invalid routes</error>');
            foreach ($invalidRoutes as $invalidRoute) {
                $output->writeln('<error>' . $invalidRoute->getName() . ' - ' . $invalidRoute->getExceptionMessage() . '</error>');
            }
        }

        if (count($unsupportedRoutes) > 0) {
            $output->writeln('<comment>' . (count($unsupportedRoutes) === 1 ? '1 route is' : count($unsupportedRoutes) . ' routes are') . ' in a format not supported by this command  - Please report the details at https://github.com/amyboyd/symfony-router-validation/issues</comment>');

            foreach ($unsupportedRoutes as $unsupportedRoute) {
                $output->writeln('<comment>' . $unsupportedRoute->getName() . ' - ' . $unsupportedRoute->getExceptionMessage() . '</comment>');
            }
        }

        $exitCode = count($invalidRoutes) || count($unsupportedRoutes) ? 1 : 0;
        return $exitCode;
    }
}
