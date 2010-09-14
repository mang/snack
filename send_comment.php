<?php
include 'comment.php';

if (isset($_POST['submit'])) {
  $comment = new Comment();
  $comment->reply_to   = urldecode($_POST['reply_to']);
  $comment->references = urldecode($_POST['references']);
  $comment->signature  = $_POST['signature'];
  $comment->email      = $_POST['email'];
  $comment->body       = $_POST['body'];

  if ($comment->validate())
    $comment->send($config_email);
  header('Location: '. $_POST['redirect']);
 }