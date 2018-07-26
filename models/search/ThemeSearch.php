<?php

namespace quoma\pageeditor\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use quoma\pageeditor\models\Theme;

/**
 * ThemeSearch represents the model behind the search form about `common\models\ThemeSearch`.
 */
class ThemeSearch extends Theme
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['theme_id', 'status'], 'integer'],
            [['name', 'slug', 'basePath', 'baseUrl'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Theme::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'theme_id' => $this->theme_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'basePath', $this->basePath])
            ->andFilterWhere(['like', 'baseUrl', $this->baseUrl]);

        return $dataProvider;
    }
}
