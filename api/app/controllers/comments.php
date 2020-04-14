<?php
namespace controllers;
use models\comments as CommentsModel;
use core\validate;
use core\telegram;
use core\image;

class comments extends authorized
{
	const LIMIT = 16;

	public function get()
	{
		if (validate::int($this->request->id, ['required' => true])) {
			$comments = new CommentsModel();
			if ($this->request->viewed == 1) {
				$comments->update($this->request->id, ['viewed' => '1']);
			}
			return $comments->get($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function add()
	{
		if (validate::string($this->request->name, ['required' => true])
		) {
			$comments = new CommentsModel();
			$comment_id = $comments->add(
				$this->requestFiltered(['item_id', 'type', 'user_id', 'rating', 'comment', 'active', 'name', 'email'])
			);

			if (!$comment_id) {
				return ['error' => 'Не удалось создать комментарий'];
			} else {
				$comment = $comments->get($comment_id);
				$telegram = new telegram();

				switch ($comment['type']) {
					case 'product':
						$telegram->sendMessage(
							[
								"chat_id" => "-362193143",
								"text" =>
									"<b>Новый комментарий на сайте!</b>\n\n".
									"Имя: $comment[name] \n".
									"Email: $comment[email] \n".
									"Рейтинг: $comment[rating] \n\n".
									"$comment[comment] \n",
								"parse_mode" => "html"
							]);
						break;
					case "article":
						break;
				}

				return $comment_id;
			}
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function update()
	{
		if (validate::int($this->request->id, ['required' => true])
			&& validate::string($this->request->name, ['required' => true])
		) {
			$comments = new CommentsModel();
			return $comments->update(
				$this->request->id,
				$this->requestFiltered(['item_id', 'type', 'user_id', 'rating', 'comment', 'active', 'name', 'email']));
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function delete()
	{
		if (validate::int($this->request->id, ['required' => true])
		) {
			$comments = new CommentsModel();
			return $comments->delete($this->request->id);
		} else {
			return ['error' => 'Ошибка валидации'];
		}
	}

	public function getList()
	{
		$comments = new CommentsModel();
		return $comments->getList($this->request->page?$this->request->page:1, self::LIMIT);
	}

	public function getListPages()
	{
		$comments = new CommentsModel();
		return $comments->getListPages(self::LIMIT);
	}

	public function getByItem()
	{
		$comments = new CommentsModel();
		return $comments->getByItem(
			$this->request->item_id,
			isset($this->request->type)?$this->request->type:'product',
			isset($this->request->page)?$this->request->page:1,
			self::LIMIT
		);
	}

	public function getRating()
	{
		$comments = new CommentsModel();
		return $comments->getRating($this->request->item_id, $this->request->type?$this->request->type:'product');
	}

	public function getUnviewed()
	{
		$comments = new CommentsModel();
		return $comments->getUnviewed($this->request->type);
	}

	public function find()
	{
		$comments = new CommentsModel();
		return $comments->find($this->request->q, $this->request->type?$this->request->type:'product');
	}

	public function getToNotify()
	{
		$comments = new CommentsModel();
		return $comments->getToNotify($this->request->type?$this->request->type:'product');
	}

	public function setNotified()
	{
		if (validate::int($this->request->id, ['required' => true])){
			$comments = new CommentsModel();
			return $comments->setNotified($this->request->id);
		}
		return ['error' => 'Ошибка валидации'];
	}
}