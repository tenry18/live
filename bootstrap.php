<?php
\EasySwoole\EasySwoole\Config::getInstance(new \EasySwoole\Config\SplArrayConfig());

\EasySwoole\EasySwoole\Command\CommandContainer::getInstance()->set(new \App\Command\InstallCommand());
