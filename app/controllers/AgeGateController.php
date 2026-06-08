<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class AgeGateController extends Controller
{
    public function show(): void
    {
        if (age_verified()) {
            $this->redirect($this->request->get('redirect', '/'));
        }
        $this->render('age-gate', ['title' => "Age Verification — Phantom Smoking"], 'minimal');
    }

    public function verify(): void
    {
        $confirm = $this->request->post('confirm');
        $dob     = $this->request->post('dob', '');
        $redirect = $this->request->post('redirect', '/');

        if ($confirm === 'yes') {
            if ($dob && !validate_age($dob, 18)) {
                $this->flash('error', 'You must be 18 or older to enter this site.');
                $this->redirect('/age-verify?redirect=' . urlencode($redirect));
            }
            Session::set('age_verified', 1);
            setcookie('age_verified', '1', time() + (30 * 86400), '/', '', false, true);
            $this->redirect($redirect ?: '/');
        } else {
            $this->redirect('https://www.google.com');
        }
    }
}
