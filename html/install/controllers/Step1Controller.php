<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

require_once __DIR__ . '/StepController.php';

class Step1Controller extends StepController
{
    public $step = 1;

    public function validate()
    {
        $platform_arr = getPlatformArray();
        $this->session->set('platform_arr', $platform_arr);
        $this->session->save();

        return true;
    }
}
