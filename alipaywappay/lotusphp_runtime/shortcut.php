<?php
//因为跟框架方法冲突改名为CB,原方法名C
function CB($className)
{
	return LtObjectUtil::singleton($className);
}
