@extends('layouts.email')

@section('content')
    <div style="text-align: center;">
        <h2 style="margin-top: 25px; margin-bottom: 25px;">
            {{ $subject }}
        </h2>
    </div>
    <div style="text-align: justify; padding: 15px 0 0 0;">
        Dobrý deň,<br/><br/>

        na základe žiadosti o spoluprácu Vám bol zaslaný registračný formulár.<br/>
        Doplňte chýbajúce údaje a overte správnosť údajov kliknutím na nasledujúci odkaz:<br/>

        <div style="margin-top: 30px; width: 100%; text-align: center">
            <a class="btn btn-primary" target="_blank" href="{{ URL::signedRoute('signed.user.candidate.form', ['candidate' => $candidate]) }}">Zobraziť formulár</a>
        </div>
    </div>
@endsection
