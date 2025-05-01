<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper
{
    public static function sendEmail($toEmail, $subject, $message, $cc = [], $bcc = [], $attachments = []): bool
    {
        // PHPMailer 객체 생성
        $mail = new PHPMailer(true);

        try {
            // SMTP 설정
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';                         // Set the SMTP server to send through
            $mail->SMTPAuth = true;                                // Enable SMTP authentication
            $mail->Username = getenv('MAIL_USERNAME');           // SMTP username (Gmail)
            $mail->Password = getenv('MAIL_PASSWORD');                 // SMTP password (app-specific password if 2FA is enabled)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    // Enable TLS encryption
            $mail->Port = 587;                                     // TCP port to connect to

            // 발신자 설정
            $mail->setFrom('TEST@gmail.com', 'TEST개발');    // 이메일 보내는 사람 (이메일과 이름)
            $mail->addAddress($toEmail);                            // 수신자 이메일 설정

            // 참조 추가 (CC) - 배열에 값이 있을 때만 추가
            if (!empty($cc)) {
                foreach ($cc as $ccEmail) {
                    $mail->addCC($ccEmail);
                }
            }

            // 숨은 참조 추가 (BCC) - 배열에 값이 있을 때만 추가
            if (!empty($bcc)) {
                foreach ($bcc as $bccEmail) {
                    $mail->addBCC($bccEmail);
                }
            }

            // 첨부 파일 추가 - 배열에 파일 경로가 있을 때만 추가
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $mail->addAttachment($file); // 파일 첨부
                }
            }

            // 네이버 이메일일 경우 EUC-KR로 인코딩, 구글은 UTF-8 그대로 사용
            if (strpos($toEmail, '@naver.com') !== false) {
                // 네이버 이메일에는 EUC-KR로 인코딩
                $encodedSubject = mb_encode_mimeheader($subject, 'EUC-KR');
            } else {
                // 기본적으로 구글 이메일에는 UTF-8로 인코딩
                $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8');
            }

            // 이메일 내용 설정
            $mail->isHTML(true);                                   // Set email format to HTML
            $mail->Subject = $encodedSubject;                             // 이메일 제목 설정
            $mail->Body    = $message;                             // 이메일 본문 내용 설정

            // 이메일 보내기
            $mail->send();

            return true; // 이메일 전송 성공
        } catch (Exception $e) {
            // 오류 발생 시 메시지 출력
            log_message('error', 'Email sending failed: ' . $mail->ErrorInfo); // 오류 로그
            return false; // 이메일 전송 실패
        }
    }
}
