<?php
// Para cerrar sesión en Vercel, "caducamos" la cookie poniéndole una fecha en el pasado.
setcookie('auth_token', '', time() - 3600, '/');

// Redirigimos al login
header('Location: login.php');
exit;
?>
