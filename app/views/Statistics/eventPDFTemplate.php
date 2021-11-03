<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Calibri';
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    table {
      width: 600px;
      margin: 0 auto;
      border-collapse: collapse;
      /*border: 2px solid;*/
    }

    .column-position {
      width: 10%;
    }

    .column-user {
      width: 70%;
    }

    .column-rating {
      width: 20%;
      text-align: right;
    }

    tr:nth-child(2n+1) {
      background: rgba(0,0,0,0.03);
    }

    table th {
      font-weight: bold;
      background: #000;
      color: #fff;
      text-align: center;
    }

    td, th {
      padding: 8px 10px;
      border: 1px solid;
    }
  </style>
</head>

<body>

<div class="header">
  <h1>Оценки по итогам взаимной экспертизы</h1>
  <h3>Аттестовано участников: <?php echo $total;?></h3>
  <h3><?php echo $workshopTitle, ' / Событие ', $event;?></h3>
</div>

<table>
  <tr>
    <th class="column-position"></th>
    <th class="column-user">Участник</th>
    <th class="column-rating">Рейтинг</th>
  </tr>
  <?php foreach ($result as $user):
    $key = array_search($user->userId, $ids);
    $fullName = $users[$key]['name'] . ' ' . $users[$key]['surname']?>
    <tr>
      <td class="column-position"><?php echo $user->rating->position;?></td>
      <td class="column-user"><?php echo $fullName;?></td>
      <td class="column-rating"><?php echo $user->rating->value;?></td>
    </tr>
  <?php endforeach;?>
</table>

</body>
</html>
