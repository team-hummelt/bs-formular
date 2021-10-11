<?php
defined( 'ABSPATH' ) or die();
/**
 * BS-Formular
 * @package Hummelt & Partner WordPress Plugin
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

?>
<div class="wp-bs-starter-wrapper">

    <div class="container">
     <div class="card card-license shadow-sm">
            <h5 class="card-header d-flex align-items-center bg-hupa py-4">
                <i class="icon-hupa-white d-block mt-2" style="font-size: 2rem"></i>&nbsp;
				<?= __( 'Forms', 'bs-formular' ) ?> </h5>
            <div class="card-body pb-4" style="min-height: 72vh">
                <div class="d-flex align-items-center">
                    <h5 class="card-title"><i
                                class="hupa-color fa fa-arrow-circle-right"></i> <?= __( 'Forms', 'bs-formular' ) ?>
                        / <span id="currentSideTitle"><?= __( 'Overview', 'bs-formular' ) ?></span>
                    </h5>
                </div>
                <hr>
                <div class="settings-btn-group d-block d-md-flex flex-wrap">
                    <button data-site="<?= __( 'Overview', 'bs-formular' ) ?>"
                            data-type="table"
                            id="btnDataTable"
                            type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseFormularOverviewSite"
                            class="btn-formular-collapse btn btn-hupa btn-outline-secondary btn-sm active" disabled>
                        <i class="fa fa-envelope-o"></i>&nbsp;
						<?= __( 'Forms', 'bs-formular' ) ?>
                    </button>

                    <button data-site="<?= __( 'Create | Edit', 'bs-formular' ) ?>"
                            data-type="formular"
                            type="button" id="formEditCollapseBtn"
                            data-bs-toggle="collapse" data-bs-target="#collapseCreateFormularSite"
                            class="btn-formular-collapse btn btn-hupa btn-outline-secondary btn-sm"><i
                                class="fa fa-align-justify"></i>&nbsp;
						<?= __( 'Create | Edit', 'bs-formular' ) ?>
                    </button>

                    <button data-site="<?= __( 'Inbox', 'bs-formular' ) ?>"
                            data-type="posteingang"
                            type="button" id="formPostEingangBtn"
                            data-bs-toggle="collapse" data-bs-target="#formPostEingangCollapse"
                            class="btn-formular-collapse btn btn-hupa btn-outline-secondary btn-sm"><i
                                class="fa fa-envelope-open-o"></i>&nbsp;
						<?= __( 'Inbox', 'bs-formular' ) ?>
                    </button>

                    <button data-site="<?= __( 'E-Mail Settings', 'bs-formular' ) ?>"
                            type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseSMTPSite"
                            class="btn-formular-collapse btn btn-hupa btn-outline-secondary btn-sm"><i
                                class="fa fa-gears"></i>&nbsp;
						<?= __( 'E-Mail Settings', 'bs-formular' ) ?>
                    </button>

                    <button data-site="<?= __( 'Examples', 'bs-formular' ) ?>"
                            type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseHelpSite"
                            class="btn-formular-collapse btn btn-hupa btn-outline-secondary btn-sm ms-auto"><i
                                class="fa fa-life-ring"></i>&nbsp;
						<?= __( 'Help', 'bs-formular' ) ?>
                    </button>
                </div>

                <hr>
                <div id="formular_display_data">
                    <!--  TODO JOB WARNING licence STARTSEITE -->
                    <div class="collapse show" id="collapseFormularOverviewSite"
                         data-bs-parent="#formular_display_data">
                        <div class="border rounded mt-1 mb-3 shadow-sm p-3 bg-custom-gray" style="min-height: 53vh">
                            <h5 class="card-title">
                                <i class="font-blue fa fa-wordpress"></i>&nbsp;<?= __( 'Inbox', 'bs-formular' ) ?>
                            </h5>
                            <hr>

                            <div id="formular-table" class="table-responsive container-fluid pb-5 pt-4">
                                <table id="TableFormulare" class="table table-striped nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th><?= __( 'Name', 'bs-formular' ) ?></th>
                                        <th><?= __( 'Shortcode', 'bs-formular' ) ?></th>
                                        <th><?= __( 'Created', 'bs-formular' ) ?></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th><?= __( 'Name', 'bs-formular' ) ?></th>
                                        <th><?= __( 'Shortcode', 'bs-formular' ) ?></th>
                                        <th><?= __( 'Created', 'bs-formular' ) ?></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div><!--overview-->

                    <!--Create Formular -->
                    <!--//TODO JOB WARNING ADD & EDIT FORM-->
                    <div class="collapse" id="collapseCreateFormularSite"
                         data-bs-parent="#formular_display_data">
                    </div><!-- End Create Formular -->

                    <!--//TODO JOB WARNING E-MAil Edit SITE-->
                    <div class="collapse" id="collapseEmailEditSite"
                         data-bs-parent="#formular_display_data">
                    </div><!-- End Create Formular -->

                    <!--//TODO JOB Meldungen SITE-->
                    <div class="collapse" id="collapseEmailMeldungenSite"
                         data-bs-parent="#formular_display_data">
                    </div>

                    <!--//TODO JOB POSTEINGANG SITE-->
                    <div class="collapse" id="formPostEingangCollapse"
                         data-bs-parent="#formular_display_data">
                    </div>

                    <!--//TODO JOB WARNING SMTP SETTINGS-->
                    <div class="collapse" id="collapseSMTPSite"
                         data-bs-parent="#formular_display_data">
                        <div class="border rounded mt-1 mb-3 shadow-sm p-3 bg-custom-gray">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title">
                                    <i class="font-blue fa fa-gears"></i>&nbsp;<?= __( 'SMTP Settings', 'bs-formular' ) ?>
                                </h5>
                                <div class="ajax-status-spinner ms-auto d-inline-block mb-2 pe-2"></div>
                            </div>
                            <hr>
                            <div class="col-xl-8 offset-xl-2 col-lg-10 offset-lg-1 col-md-12 pb-3">
                                <form class="send-bs-form-auto-save-ajax-formular" action="#" method="post">
                                    <input type="hidden" name="method" value="smtp_settings">
                                    <div class="row">
                                        <div class="col-lg-6 col-12 mb-3">
                                            <label for="emailABSInput" class="form-label">
												<?= __( 'Name oder Firma des Absenders:', 'bs-formular' ) ?> </label>
                                            <input type="text" class="form-control"
                                                   value="<?= get_option( 'email_abs_name' ) ?>"
                                                   name="email_abs_name"
                                                   id="emailABSInput">
                                            <div id="helpEmailABSInput" class="form-text">Wenn der Eintrag leer bleibt,
                                                wird
                                                der Seitentitel verwendet.
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-12"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-12 mb-3">

                                            <label for="absEmailInput" class="form-label">
												<?= __( 'Absender E-Mail:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="email" class="form-control"
                                                   value="<?= get_option( 'bs_abs_email' ) ?>"
                                                   name="email_adresse"
                                                   id="absEmailInput">
                                            <div id="helpEbsEmailInput" class="form-text">In den meisten Fällen, muss
                                                hier
                                                die Provider-E-Mail eingegeben werden.
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-12 mb-3">
                                            <label for="smtpHostInput" class="form-label">
												<?= __( 'SMTP Host:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                   value="<?= get_option( 'bs_form_smtp_host' ) ?>"
                                                   placeholder="mail.gmx.net"
                                                   name="smtp_host" id="smtpHostInput">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-12 mb-3">

                                            <label for="smtpPortInput" class="form-label">
												<?= __( 'SMTP Port:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="number" class="form-control"
                                                   value="<?= get_option( 'bs_form_smtp_port' ) ?>" placeholder="587"
                                                   name="smtp_port" id="smtpPortInput">

                                        </div>

                                        <div class="col-lg-6 col-12 mb-3">
                                            <label for="smtpSecureInput" class="form-label">
												<?= __( 'SMTP Secure:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                   value="<?= get_option( 'bs_form_smtp_secure' ) ?>" placeholder="tls"
                                                   name="smtp_secure" id="smtpSecureInput"
                                                   autocomplete="cc-number">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-12 mb-3">

                                            <label for="emailUserInput" class="form-label">
												<?= __( 'Benutzername:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="text" class="form-control"
                                                   value="<?= get_option( 'bs_form_email_benutzer' ) ?>"
                                                   name="email_benutzer"
                                                   id="emailUserInput" autocomplete="cc-number">

                                        </div>
                                        <div class="col-lg-6 col-12 mb-3">
                                            <label for="emailPWInput" class="form-label">
												<?= __( 'Passwort:', 'bs-formular' ) ?> <span
                                                        class="text-danger">*</span></label>
                                            <input type="password" class="form-control"
                                                   placeholder="xxxxxxxxxxxxxxxxxxxxxx"
                                                   name="email_passwort"
                                                   id="emailPWInput" autocomplete="cc-number">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-6 col-12 mb-3">
                                            <div class="form-check form-switch">
                                                <input onclick="this.blur()" class="form-check-input" type="checkbox"
                                                       name="smtp_auth_check"
                                                       id="smtpAuthChecked" <?= ! get_option( 'bs_form_smtp_auth_check' ) ?: 'checked' ?>>
                                                <label class="form-check-label"
                                                       for="smtpAuthChecked"><?= __( 'SMTP Authentifizierung:', 'bs-formular' ) ?></label>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-12 mb-3"></div>
                                    </div>
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input onclick="this.blur()" name="email_aktiv" class="form-check-input"
                                               type="checkbox"
                                               id="checkMailAktiv" <?= ! get_option( 'email_empfang_aktiv' ) ?: 'checked' ?>>
                                        <label class="form-check-label" for="checkMailAktiv">E-Mail speichern
                                            aktiv</label>
                                    </div>
                                    <hr>
                                </form>
                                <button id="load-smtp-check" class="btn btn-blue btn-sm" type="button">
                                    <i class="fa fa-gears"></i>&nbsp;
                                    SMTP Test
                                </button>
                            </div>
                        </div>
                    </div><!--smtp-->

                    <!--//TODO JOB WARNING HELP SITE-->
                    <div class="collapse" id="collapseHelpSite"
                         data-bs-parent="#formular_display_data">
                        <div class="border rounded mt-1 mb-3 shadow-sm p-3 bg-custom-gray">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title">
                                    <i class="font-blue fa fa-life-ring"></i>&nbsp;<?= __( 'Hilfe', 'bs-formular' ) ?>
                                </h5>

                            </div>
                            <hr>

                            <div class="my-3 p-3 bg-body rounded shadow-sm help-wrapper">
                                <h6 class="border-bottom pb-2 mb-0">Grundlegender Aufbau</h6>
                                <p class="fs-6">
                                    <strong class="d-block text-gray-dark pb-2">Aufbau</strong>

                                    <span class="d-block lh-sm text-muted fs-6">
                                            [label] <b class="text-danger"> text-Label</b><br>
                                            [type=text]  <b class="text-danger">your-text</b>]
                                        </span>
                                </p>
                                <p>
                                    <b class="text-danger">Text Label</b> ist die Label Bezeichnung für das
                                    Formularfeld.<br>
                                    Der Wert <b class="text-danger">your-text</b> wird zum erstellen der E-Mail
                                    Benachrichtigung verwendet.
                                    <span class="pt-2 d-block small">
                                           * Die eckigen Klammern dürfen nicht entfernt werden.
                                        </span>
                                </p>
                                <hr>
                                <strong class="d-block text-gray-dark pb-2">Beispiel Felder mit Ausgabe</strong>
                                <span class="d-block mt-3">
                                        <pre class="mb-1 pb-0">
[label] Vorname
[type=text]  vorname]

[label] Beschreibung
[type=textarea-<b class="text-danger">3</b>] beschreibung]

[label] Test aktiv
[type=checkbox]  test]

[label] Lieblingsfarbe
[type=radio-default]  Orange<b class="text-danger">*</b>, Gelb, Rot]

[label] Senden
[type=button] submit]
            </pre>
                <span class="d-block small my-0 pt-0">
            * Textarea Rows werden mit einer Zahl im type Feld angegeben. In diesem Beispiel <b
                            class="text-danger">3</b> Rows.
                </span>
             <span class="d-block small my-0 pt-0">
                 * Select, Radio und Checkboxen können mit einem <b class="text-danger">*</b> hinter der Bezeichnung als <i>checked</i> bzw. als <i>selected</i> ausgegeben werden.
               </span>
            </span>
                                <hr>
                                <h5>Ausgabe im Frontend</h5>
                                <div class="col-12 col-lg-4 pt-3">
                                    <!----------->
                                    <div class="mb-3"><label class="form-label mb-1"
                                                             for="a94e76fb3e71">Vorname </label><input type="text"
                                                                                                       class="form-control"
                                                                                                       name="a94e76fb3e71"
                                                                                                       id="a94e76fb3e71">
                                    </div>
                                    <div class="mb-3"><label class="form-label mb-1"
                                                             for="f4c7d12158e3">Beschreibung </label><textarea
                                                name="f4c7d12158e3" class="form-control" id="f4c7d12158e3"
                                                rows="3"></textarea></div>
                                    <div class="mb-3">
                                        <div class="form-check"><input onclick="this.blur()"
                                                                       class="form-check-input" name="d3c00ef717a5"
                                                                       type="checkbox" id="d3c00ef717a5"><label
                                                    class="form-check-label" for="d3c00ef717a5">Test aktiv</label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check form-check-inline"><input onclick="this.blur()"
                                                                                         class="form-check-input"
                                                                                         type="radio"
                                                                                         name="762904ddb30a"
                                                                                         id="642260e2ecac"
                                                                                         value="642260e2ecac"
                                                                                         checked=""><label
                                                    class="form-check-label" for="642260e2ecac">Orange</label></div>
                                        <div class="form-check form-check-inline"><input onclick="this.blur()"
                                                                                         class="form-check-input"
                                                                                         type="radio"
                                                                                         name="762904ddb30a"
                                                                                         id="b9553b19d58d"
                                                                                         value="b9553b19d58d"><label
                                                    class="form-check-label" for="b9553b19d58d">Gelb</label></div>
                                        <div class="form-check form-check-inline"><input onclick="this.blur()"
                                                                                         class="form-check-input"
                                                                                         type="radio"
                                                                                         name="762904ddb30a"
                                                                                         id="fff47dea184e"
                                                                                         value="fff47dea184e"><label
                                                    class="form-check-label" for="fff47dea184e">Rot</label></div>
                                    </div>
                                    <div class="d-block">
                                        <button onclick="this.blur()" id="8d1655cb2113" name="8d1655cb2113"
                                                type="submit" class="btn btn-secondary">Senden
                                        </button>
                                    </div>

                                    <!----------->
                                    <hr>

                               <!-- </div>
                                <h5 class="pb-3 pt-4">Ausgabe Backend</h5>
                                <img class="img-fluid" src="<?= BS_FORMULAR_PLUGIN_URL . '/assets/images/1.jpg' ?>">

                                <hr>-->
                                <h5>Pflichtfelder</h5>
                                <hr>
                                <pre class="mb-0 pb-0">
<b class="text-danger">Input Felder</b>
[label] Vorname
[type=text<b class="text-danger">*</b>]  vorname]

<b class="text-danger">Textarea</b>
[label] Beschreibung
[type=textarea-3<b class="text-danger">*</b>] beschreibung]

<b class="text-danger">Checkbox</b>
[label] Test aktiv<b class="text-danger">*</b>
[type=checkbox]  test]

<b class="text-danger">Select Feld</b>
[label] Bitte auswählen
[type=select<b class="text-danger">*</b>]  erste Auswahl, Auswahl-2, Auswahl-3]
 </pre>

                                Pflichtfelder werden mit einem <b class="text-danger">*</b> gekennzeichnet. Radio Input
                                Felder können nicht als Pflichtfeld
                                gekennzeichnet werden, weil in der Regel ein Feld immer aktiv ist.

                                <hr>
                                <h5>Datenschutz akzeptieren mit Link</h5>
                                <hr>

                                <pre>
[label] Ich akzeptiere die <b class="text-danger"> # </b> Datenschutzerklärung
[type=dataprotection] <span class="text-primary"> https://start.hu-ku.com/theme-update</span>]
                                </pre>
                                <p>
                                    <span class="d-block small"> Hinter der Raute <b class="text-danger">#</b> wird der Linktext eingefügt.</span>
                                    <span class="d-block small">Bei <i>dataprotection</i> wird die URL z.B. zur Datenschutzerklärung eingefügt.</span>
                                </p>
                                <h6>Ausgabe des Beispiels</h6>
                                <hr>
                                <div class="mb-3">
                                    <div class="form-check dscheck"><input onclick="this.blur()"
                                                                           class="form-check-input"
                                                                           data-id="1532472007ae" name="dscheck"
                                                                           type="checkbox" id="1532472007ae"
                                                                           required=""><label class="form-check-label"
                                                                                              for="1532472007ae">Ich
                                            akzeptiere die <a
                                                    href="https://start.hu-ku.com/theme-update/">
                                                Datenschutzbestimmungen</a><span class="text-danger"> *</span> </label>
                                        <div class="invalid-feedback">Sie müssen die Bedingungen akzeptieren, bevor Sie
                                            Ihre Nachricht senden.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--parent-->
            </div><!--card-->
            <small class="card-body-bottom" style="right: 1.5rem">Version: <i
                        class="hupa-color">v<?= BS_FORMULAR_PLUGIN_VERSION ?></i></small>
        </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="formDeleteModal" tabindex="-1" aria-labelledby="formDeleteModal"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-hupa">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i
                                class="text-danger fa fa-times"></i>&nbsp; Abbrechen
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="btn-delete-form btn btn-danger">
                        <i class="fa fa-trash-o"></i>&nbsp; löschen
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!--Modal-->
    <div class="modal fade" id="btnIconModal" tabindex="-1" aria-labelledby="btnIconModal"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-xl-down modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-hupa">
                    <h5 class="modal-title"
                        id="exampleModalLabel"><?= __( 'BS-Formular', 'bs-formular' ); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="icon-grid"></div>
                    <div id="email-template"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i
                                class="text-danger fa fa-times"></i>&nbsp; Schließen
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="snackbar-success"></div>
<div id="snackbar-warning"></div>