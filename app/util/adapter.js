class Adapter{
    data
    size
    container
    Layout
    index=0;
    lastIndex;
    offset;

    constructor(data,container,Layout,offset){
        this.data=data;
        this.container=container;
        this.Layout=Layout;
       
        this.offset=offset;
        this.size=this.data.length;
        this.lastIndex=Math.floor(this.size/this.offset);
    }

    bind(pieces){
        this.clearList();
       
        pieces.forEach(element => {
            this.container.innerHTML+=this.Layout(element);
        });
    }

    setOffsetRange(val,callback){
        this.offset=val;
        this.lastIndex=Math.floor(this.size/this.offset);
        this.firstPage(callback);
    }

    clearList(){
        this.container.innerHTML="";
    }

    setData(index, callback){
        var off=index*this.offset;
        var row=off+this.offset;
        this.clearList();
        this.bind(this.data.slice(off,row));
        if(row>this.data.length) row=this.size;
        var info=`${off+1} - ${row} of ${this.size}`;
        callback(info);
    }

    firstPage(callback){
        this.index=0;
        this.setData(this.index, callback)
    }

    lastPage(callback){
        this.index=this.lastIndex;
        this.setData(this.index, callback)
    }

    nextPage(callback){
        
        if(this.lastIndex>this.index && this.size>this.offset){
            this.index++;
            console.log(this.index);
            this.setData(this.index,callback);
        }else{
            console.log('somethine wroung');
        }
        var info=``;
    }

    prevPage(callback){
        if(this.index>0){
            this.index--;
            console.log(this.index);
            this.setData(this.index,callback);
        }
    }

}

export {Adapter};