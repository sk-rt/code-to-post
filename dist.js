const cpx = require("cpx");
const destTrunk = "./svn/trunk/"
const destAssets = "./svn/"
const srcDir = "./";
const config = [
	{
		dir: "",
		files: "code-to-post.php",
		destDir: destTrunk,
	},
	{
		dir: "",
		files: "readme.txt",
		destDir: destTrunk,
	},
	{
		dir: "assets/",
		files: "*",
		destDir: destAssets,
	},
	{
		dir: "languages/",
		files: "*",
		destDir: destTrunk,
	},
]
console.log(config);
config.forEach(function(obj){
	cpx.copy(
		srcDir + obj.dir + obj.files,
		obj.destDir + obj.dir,
		{
			clean: true
		}
	);
})
