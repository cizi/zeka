<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class ContactForm  {

    use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/**
	 * @param FormFactory $factory
	 */
	public function __construct(FormFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * @return Form
	 */
	public function create() {
		$form = $this->factory->create();
		$form->getElementPrototype()->addAttributes(["onsubmit" => "return requiredFields();"]);

		$form->addText("name")
			->setAttribute("tabindex", "1")
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", CONTACT_FORM_NAME)
			->setRequired(CONTACT_FORM_NAME_REQ)
            ->setAttribute("style", "margin-top: 5px;");

		$form->addText("contactEmail")
			->setAttribute("type","email")
			->setAttribute("tabindex", "2")
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", CONTACT_FORM_EMAIL)
			->setRequired(CONTACT_FORM_EMAIL_REQ)
            ->setAttribute("style", "margin-top: 5px;");

		$form->addText("subject")
			->setAttribute("tabindex", "3")
			->setAttribute("class", "form-control")
			->setAttribute("placeholder", CONTACT_FORM_SUBJECT)
			->setRequired(CONTACT_FORM_SUBJECT_REQ)
            ->setAttribute("style", "margin-top: 5px;");

        $form->addCheckbox("gdpr", CONTACT_FORM_GDPR)
            ->setAttribute("tabindex", "4")
            ->setRequired(CONTACT_FORM_GDPR_REQ)
            ->setAttribute("style", "margin-top: 5px;");

		$form->addUpload("attachment")
			->setAttribute("tabindex", "5")
			->setAttribute("placeholder", CONTACT_FORM_ATTACHMENT)
			->setAttribute("class", "form-control contactForm")
            ->setAttribute("style", "margin-top: 5px;");

		$form->addTextArea("text", null, null, 7)
			->setAttribute("tabindex", "6")
			->setAttribute("placeholder", CONTACT_FORM_TEXT)
			->setRequired(CONTACT_FORM_TEXT_REQ)
			->setAttribute("class", "form-control")
			->setAttribute("style", "margin-top: 5px; margin-left: 5px;");

		$form->addSubmit("confirm", CONTACT_FORM_BUTTON_CONFIRM)
			->setAttribute("tabindex", "7")
			->setAttribute("class","btn btn-success");

		return $form;
	}
}
