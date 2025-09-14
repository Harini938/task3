<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }
include 'dbcon.php';
$id =(int) $_GET['id'];
$conn->query("DELETE FROM posts WHERE id=$id");
header("Location: posts.php");
?>