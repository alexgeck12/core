<?php
namespace controllers;

use core\validate;
use core\telegram;
use models\feedback as FeedbackModel;

class feedback extends authorized
{
	const LIMIT = 100;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$feedback = new FeedbackModel();
			$feedback->update($this->request->id, ['viewed' => '1']);
			return $feedback->get($this->request->id);
		}
		throw new \Error ('Ошибка валидации');
	}

	public function add()
	{
		$this->request->phone = preg_replace("/[^0-9]/", '', $this->request->phone);

		if (validate::string($this->request->name, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])
			&& validate::phone($this->request->phone, ['required' => true])) {

			$feedback = new FeedbackModel();
			$feedback_id = $feedback->add($this->requestFiltered(['name', 'phone', 'email', 'message', 'type']));

			if (!$feedback_id) {
				return ['error' => 'Не удалось создать заявку'];
			} else {
				$feedback = $feedback->get($feedback_id);
				$telegram = new telegram();

				switch ($feedback['type']) {
					case "recall":
						$telegram->sendMessage(
							[
								"chat_id" => "-362193143",
								"text" =>
									"<b>На сайте был заказан обратный звонок:</b>\n\n".
									"Имя: $feedback[name] \n".
									"Телефон: $feedback[phone] \n",
								"parse_mode" => "html"
							]);
						break;
					case "message":
						$telegram->sendMessage(
							[
								"chat_id" => "-362193143",
								"text" =>
									"<b>Запрос на консультацию № $feedback[id]</b>\n\n" .
									"Страница: ".$this->request->service_name . "\n\n" .
									"Время: $feedback[created] \n" .
									"Имя: $feedback[name] \n" .
									"Телефон: $feedback[phone] \n" .
									"Комментарий: $feedback[message] \n",
								"parse_mode" => "html"
							]);

						break;
				}

				return $feedback_id;
			}
		}

		return ['error' => 'Ошибка валидации'];
	}

	public function update()
	{
		if (validate::int($this->request->id, ['required' => true])
			&& validate::string($this->request->name, ['required' => true])
			&& validate::phone($this->request->phone, ['required' => true])
			&& validate::string($this->request->type, ['required' => true])) {
			$feedback = new FeedbackModel();
			return $feedback->update(
				$this->request->id,
				$this->requestFiltered(['name', 'phone', 'email', 'message', 'type'])
			);
		}
		throw new \Error ('Ошибка валидации');
	}

	public function delete()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$feedback = new FeedbackModel();
			return $feedback->delete($this->request->id);
		}
		throw new \Error ('Ошибка валидации');
	}

	public function getAll()
	{
		$feedback = new FeedbackModel();
		return $feedback->getAll($this->request->type);
	}

	public function getList()
	{
		$feedback = new FeedbackModel();
		return $feedback->getList($this->request->page?$this->request->page:1, self::LIMIT, $this->request->type);
	}

	public function getListPages()
	{
		$feedback = new FeedbackModel();
		return $feedback->getListPages(self::LIMIT, $this->request->type);
	}

	public function getUnviewed()
	{
		$feedback = new FeedbackModel();
		return $feedback->getUnviewed($this->request->type);
	}

	public function find()
	{
		$feedback = new FeedbackModel();
		return $feedback->find($this->request->q, $this->request->type?$this->request->type:'message');
	}

	public function getToNotify()
	{
		$feedback = new FeedbackModel();
		return $feedback->getToNotify($this->request->type?$this->request->type:'message');
	}

	public function setNotified()
	{
		if (validate::int($this->request->id, ['required' => true])){
			$feedback = new FeedbackModel();
			return $feedback->setNotified($this->request->id);
		}
		throw new \Error ('Ошибка валидации');
	}
}