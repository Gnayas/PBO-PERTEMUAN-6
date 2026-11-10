<?php
require_once __DIR__ . '/MahasiswaReguler.php';

$m = new MahasiswaReguler('2530521067', 'Gnayas', 'Sains dan Teknologi',
    'Teknik Informatika', 'SMAN 4 Sukabumi', 4, 'Band', 'Islam', 5000000);

echo $m->nim;   // Warning: Undefined property (private milik parent)
echo $m->ukt;   // Fatal error: Cannot access private property  