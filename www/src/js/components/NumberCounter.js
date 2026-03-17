/**
 * Use: <span data-number-counter="true" data-number-counter-delay="300" data-number-counter-tick="20">150</h2>
 * data-number-counter-delay -> optional delay time
 * data-number-counter-tick -> optional tick interval 
 */
class NumberCounter {
	constructor(container) {
		const TICK_INTERVAL = 50;
		
		this.container = $(container);
		this.currentValue = 0;
		this.finalValue = parseInt(this.container.text());
		this.startDelay = parseInt(this.container.data('numberCounterDelay'));
		this.tickDelay = this.container.data('numberCounterTick') ? parseInt(this.container.data('numberCounterTick')) : TICK_INTERVAL;
		this.tickInterval;
		
		// set initial value
		this.update();
		
		// start delay
		setTimeout(() => {
			// tick delay
			this.tickInterval = setInterval(() => {
				this.count();
			}, this.tickDelay);
			
		}, this.startDelay);
	}
	
	
	update(){
		this.container.text(String(this.currentValue));
	}
	
	
	count(){
		if(this.currentValue < this.finalValue){
			this.currentValue++;
			this.update();
		} else {
			clearInterval(this.tickInterval);
			this.container.parent().find('.counterTempHiddenElement').addClass('showHiddenElement');
		}
	}

}
