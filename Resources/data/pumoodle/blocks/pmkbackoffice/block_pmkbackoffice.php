<?php

class block_pmkbackoffice extends block_base
{
    public function init()
    {
        $this->title = get_string('pmkbackoffice', 'block_pmkbackoffice');
    }

    //Prevents the block from appearing anywhere. (since we only use it to have the back-office show into an iframe.)
    public function applicable_formats()
    {
        return array(
            'all' => false,
            'nowhere' => true, //WORKAROUND: If the returned array does not contain something set to 'true', the plugin cannot be installed.
        );
    }
}
