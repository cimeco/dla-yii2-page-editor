<?php

namespace quoma\pageeditor\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use quoma\pageeditor\models\BoxClass;

/**
 * BoxClassSearch represents the model behind the search form about `quoma\pageeditor\models\BoxClass`.
 */
class BoxClassSearch extends BoxClass
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['box_class_id', 'status'], 'integer'],
            [['class'], 'safe'],
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
        $query = BoxClass::find();

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
            'box_class_id' => $this->box_class_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'class', $this->class]);

        return $dataProvider;
    }
}