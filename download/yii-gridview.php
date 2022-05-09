<?php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

class Test ｛
   public function indexAction()
   {

     $dataProvider = new ActiveDataProvider([
        'query' => supplier::find(),
        'pagination' => [
        'pageSize' => 50,
        ],
      ]);
      return GridView::widget([
           'dataProvider' => $dataProvider,
      'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'id',
            'name',
            'code',
            'status',
        ],
]);

   }
｝
