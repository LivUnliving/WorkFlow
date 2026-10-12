<?php
// Para demonstracao SOMENTE em localhost: mude para true. Nunca use true em producao.
define('WORKFLOW_DEMO_RESET', false);
// Arquivo local nao versionado: config/email.local.php define variaveis SMTP.
if (is_file(__DIR__ . '/email.local.php')) require __DIR__ . '/email.local.php';
function enviarEmailRecuperacao($email, $url) {
    $texto = "Voce solicitou a troca de senha do WorkFlow.\n\nAbra o link (valido por 30 minutos):\n" . $url . "\n\nSe nao foi voce, ignore este email.";
    $vendor = __DIR__ . '/../vendor/autoload.php';
    if (is_file($vendor) && defined('WORKFLOW_SMTP_HOST') && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        try {
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = WORKFLOW_SMTP_HOST;
            $mailer->SMTPAuth = true;
            $mailer->Username = WORKFLOW_SMTP_USER;
            $mailer->Password = WORKFLOW_SMTP_PASS;
            $mailer->Port = WORKFLOW_SMTP_PORT;
            $mailer->SMTPSecure = WORKFLOW_SMTP_PORT === 465 ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom(WORKFLOW_FROM_EMAIL, 'WorkFlow');
            $mailer->addAddress($email);
            $mailer->Subject = 'WorkFlow - recuperar senha';
            $mailer->Body = $texto;
            return $mailer->send();
        } catch (Throwable $e) { error_log('WorkFlow SMTP: ' . $e->getMessage()); return false; }
    }
    return mail($email, 'WorkFlow - recuperar senha', $texto, 'From: no-reply@exemplo.com');
}
function ambienteDemoLocal() {
    return WORKFLOW_DEMO_RESET && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true);
}
?>
