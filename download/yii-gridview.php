<?php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

class filterModel {

}
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
           'filterModel' =>
           'dataProvider' => $dataProvider,
      'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            ['attribute'=>'id',
            'filter'=> ['1'=>'>10', 
                    '2'=>'>=10', 
                    '3'=>'<10',
                    '4'=>'<=10',
                     ],],
           ['attribute'=> 'name', 
           'filter'=> ''],
           ['attribute'=> 'code',
           'filter'=> ''],
            ['attribute'=>'status',
            'filter' =>['ok','hold'],
            ]
        ],
]);

   }
｝
