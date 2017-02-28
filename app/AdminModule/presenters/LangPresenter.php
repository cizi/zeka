<?php

namespace App\AdminModule\Presenters;

use App\Forms\LangForm;
use App\Forms\LangItemForm;
use App\Model\LangRepository;
use App\Model\WebconfigRepository;

class LangPresenter extends SignPresenter {

	/** @var LangForm $langForm */
	private $langForm;

	/** @var LangItemForm */
	private $langItemForm;

	/** @var LangRepository */
	private $langRepository;

	/** @var WebconfigRepository */
	private $webconfigRepository;

	public function __construct(LangForm $langForm, LangItemForm $langItemForm, LangRepository $langRepository, WebconfigRepository $webconfigRepository) {
		$this->langForm = $langForm;
		$this->langItemForm = $langItemForm;
		$this->langRepository = $langRepository;
		$this->webconfigRepository = $webconfigRepository;
	}

	public function renderDefault() {
		$defaults = $this->webconfigRepository->load(WebconfigRepository::KEY_LANG_FOR_COMMON);
		/*foreach ($defaultsCommon as $key => $value) {
			$defaults[$key] = $value;
		} */
		$this['langForm']->setDefaults($defaults);

		$this->template->langFlagKey = LangRepository::KEY_LANG_ITEM_FLAG;
		$this->template->langMutations = $this->langRepository->findLanguagesWithFlags();
	}

	public function createComponentLangForm() {
		$form = $this->langForm->create();
		$form->onSuccess[] = $this->saveLangCommon;

		return $form;
	}

	public function saveLangCommon($form, $values) {
		foreach ($values as $key => $value) {
			if ($value != "") {
				$this->webconfigRepository->save($key, $value, WebconfigRepository::KEY_LANG_FOR_COMMON);
			}
		}
	}

	public function createComponentLangItemForm() {
		$form = $this->langItemForm->create();
		$form->onSuccess[] = $this->saveLangItem;

		return $form;
	}

	public function saveLangItem() {

	}

	public function actionDelete($id) {
		$this->redirect('default');
	}
}