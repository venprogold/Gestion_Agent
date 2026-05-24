<?php
// generate_hashes.php
echo "Hash pour 'responsable123' : " . password_hash('responsable123', PASSWORD_DEFAULT) . "\n";
echo "Hash pour 'agent123' : " . password_hash('agent123', PASSWORD_DEFAULT) . "\n";
echo "\n";
echo "Copie ces hashs dans ton fichier database.sql !\n";
?>