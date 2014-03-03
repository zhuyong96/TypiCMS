<?php namespace TypiCMS\Modules\Translations\Repositories;

use DB;
use App;
use Config;

use Illuminate\Database\Eloquent\Model;

use TypiCMS\Repositories\RepositoriesAbstract;

class EloquentTranslation extends RepositoriesAbstract implements TranslationInterface {


	// Class expects an Eloquent model
	public function __construct(Model $model)
	{
		$this->model = $model;
	}


	/**
	 * Get all models with categories
	 *
	 * @param boolean $all Show published or all
     * @return StdClass Object with $items
	 */
	public function getAll($all = false, $relid = null)
	{
		$query = $this->model->with('translations');
		
		$data = array();

		$models = $query->get();
		foreach ($models as $model) {
			$data[$model->id]['id'] = $model->id;
			$data[$model->id]['key'] = $model->key;
			foreach ($model->translations as $translation) {
				$data[$model->id][$translation->locale] = $translation->translation;
			}
		}
		return $data;
	}


	/**
	 * Create a new model
	 *
	 * @param array  Data to create a new object
	 * @return boolean
	 */
	public function create(array $data)
	{
		if ( $model = $this->model->create($data) ) {
			// Save to json
			$this->saveToJSON();
			return $model;
		}
		return false;
	}

	
	/**
	 * Save to JSON
	 *
	 * @return boolean
	 */
	public function saveToJSON()
	{
		$translations = $this->getAllToArray();
		dd($translations);
	}


	/**
	 * Update an existing model
	 *
	 * @param array  Data to update a model
	 * @return boolean
	 */
	public function update(array $data)
	{
		$model = $this->model->find($data['id']);
		$model->fill($data);
		$model->save();
		return true;
	}


	/**
	 * Get translations to Array
	 *
	 * @return array
	 */
	public function getAllToArray($locale = null)
	{
		$query = $this->model->join('translation_translations', 'translations.id', '=', 'translation_translations.translation_id');
		if ($locale) {
			$query->where('locale', $locale);
			return $query->lists('translation', 'key');
		}
		$models = $query->orderBy('locale')->get();
		$array = array();
		foreach ($models as $model) {
			$array[$model->locale][$model->key] = $model->translation;
		}
		// dd($array);
	}


}