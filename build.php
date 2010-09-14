<?php
include 'comment.php';
$thread_id = $argv[1];
$path = $config_maildir . $thread_id;

// move from new to cur
$maildir_new = opendir($path."/new");

while ($file = readdir($maildir_new)) {
  if ($file[0] != ".") {
    rename("$path/new/$file", "$path/cur/$file");
  }
 }

// build tree from cur
$maildir_cur = opendir($path."/cur");
$comment_list = Array();

function get_header($name, $headers) {
  $matches = Array();
  preg_match("/$name: (.*)/", $headers, $matches);
  return $matches[1];
}

while ($file = readdir($maildir_cur)) {
  if ($file[0] != ".") {
    list($headers, $body) = split("\n\n", file_get_contents("$path/cur/$file"), 2);
    
    $comment = new Comment();
    $comment->reply_to   = get_header("In-Reply-To", $headers);
    $comment->message_id = get_header("Message-Id", $headers);
    $comment->references = get_header("References", $headers);
    $comment->email      = get_header("From", $headers);
    $comment->body       = $body;

    array_push($comment_list, $comment);
  }
 }

// comment rendering

function render_reply_form($comment) {
  $message_id = htmlspecialchars($comment->message_id);
  $references = htmlspecialchars($comment->references) . " $message_id";
  return <<<HTML
<a href="#" onclick="javascript:document.getElementById('$message_id').style.display='block';return false;">reply</a><br/>
<form method="post" action="send_comment.php" class="snack_form" id="$message_id">
  <table>
    <tr>
      <td>
        <input type="hidden"    name="reply_to"   value="$message_id"/>
        <input type="hidden"    name="references" value="$references"/>
        <input type="hidden"    name="redirect"   value="$config_redirect_url"/>
        <label for="signature">signatur</label>
      </td>
      <td><input type="text"      name="signature"/></td>
    </tr>
    <tr>
      <td><label for="email">epost (publiceras ej)</label></td>
      <td><input type="text"      name="email"/></td>
    </tr>
    <tr>
      <td colspan="2"><textarea name="body" cols="80" rows="20"></textarea></td>
    </tr>
    <tr>
      <td><input type="submit" name="submit"/></td>
    </tr>
  </table>
</form>
HTML;
}

function render_comment($comment, $list) {
  $string =  '<div class="snack_comment">';
  if ($comment->body)
    $string .= $comment->body . "<br/>";
  $string .= render_reply_form($comment);
  foreach ($list as $subcomment) {
    if ($subcomment->reply_to == $comment->message_id) {
      $string .= render_comment($subcomment, $list);
    }
  }
  return $string . "</div>\n";
}

// build the tree

$article = new Comment();
$article->message_id = "<$argv[1]>";
$article->references = "<$argv[1]>";

$tree = render_comment($article, $comment_list);

$handle = fopen("$config_write_dir$thread_id.html", "w") or die("could not write file");
fwrite($handle, $tree);
fclose($handle);