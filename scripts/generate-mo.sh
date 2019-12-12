for file in `find . "./languages" -name "*.po"` ; do msgfmt -o `echo $file | sed "s/\.po/\.mo/"` $file ; done
