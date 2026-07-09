<?php
header("Location: ../audit_pemeriksaan/create.php" . (isset($_GET['program_id']) ? '?program_id=' . (int)$_GET['program_id'] : ''));
exit;
