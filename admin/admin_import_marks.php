<?php

session_name("staff");
session_start();
header("Location: admin_import_data.php?tab=marks");
exit;
?>